<?php
set_time_limit(0);

$ollama_host = $_ENV['OLLAMA_HOST'] ?? getenv('OLLAMA_HOST') ?: 'http://localhost:11434';
$ollama_host = rtrim($ollama_host, '/');

$action = $_GET['action'] ?? '';

if (empty($action)) {
    header('Content-Type: text/event-stream; charset=UTF-8');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    header('X-Accel-Buffering: no');
} else {
    header('Content-Type: application/json; charset=UTF-8');
}

// --- API ACTIONS PARA MANIPULAÇÃO DE MODELOS DO OLLAMA (Isolando do Chat) ---

if ($action === 'list_models') {
    $url = "$ollama_host/api/tags";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response !== false) {
        echo $response;
    } else {
        echo json_encode(['models' => []]);
    }
    exit;
} 

elseif ($action === 'list_running_models') {
    $url = "$ollama_host/api/ps";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $response !== false) {
        echo $response;
    } else {
        echo json_encode(['models' => []]);
    }
    exit;
} 

elseif ($action === 'run_model') {
    $selected_model = $_POST['model'] ?? $_GET['model'] ?? '';
    if (empty($selected_model)) {
        echo json_encode(['error' => 'Modelo não fornecido']);
        exit;
    }

    // 1. Obter modelos rodando atualmente
    $ps_url = "$ollama_host/api/ps";
    $ch = curl_init($ps_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $ps_response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $running_models = [];
    if ($httpCode === 200 && $ps_response !== false) {
        $ps_data = json_decode($ps_response, true);
        if (isset($ps_data['models']) && is_array($ps_data['models'])) {
            foreach ($ps_data['models'] as $m) {
                $running_models[] = $m['name'];
            }
        }
    }

    // Normalização de nomes dos modelos
    $normalize = function($name) {
        return strtolower(trim(str_replace(':latest', '', $name)));
    };

    $target_norm = $normalize($selected_model);
    $unloaded = [];

    // 2. Descarregar os outros modelos ativos
    foreach ($running_models as $running) {
        if ($normalize($running) !== $target_norm) {
            $unload_url = "$ollama_host/api/generate";
            $unload_data = json_encode([
                "model" => $running,
                "keep_alive" => 0
            ]);

            $ch = curl_init($unload_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $unload_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
            $unloaded[] = $running;
        }
    }

    // 3. Carregar o modelo solicitado na VRAM
    $load_url = "$ollama_host/api/generate";
    $load_data = json_encode([
        "model" => $selected_model,
        "keep_alive" => -1
    ]);
    $ch = curl_init($load_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $load_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $load_response = curl_exec($ch);
    $loadCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo json_encode([
        'success' => ($loadCode === 200 && $load_response !== false),
        'loaded' => $selected_model,
        'unloaded' => $unloaded
    ]);
    exit;
}

// --- FLUXO PRINCIPAL: ANÁLISE DE PETIÇÃO ---

$petition_text = $_POST['prompt'] ?? '';
$model = $_POST['model'] ?? 'llama3:latest';

if (empty($petition_text)) {
    echo json_encode(["error" => "Texto da petição não fornecido."]);
    exit;
}

// Prompt minimalista instruindo a preencher apenas o JSON
$system_prompt = <<<PROMPT
Você é um assistente jurídico de inteligência artificial encarregado de ler petições iniciais ou documentos de processos judiciais e convertê-los em dados puramente estruturados em formato JSON.

Você deve retornar APENAS o objeto JSON, sem nenhum texto explicativo anterior ou posterior, sem formatação markdown (como blocos de código com ```json) e sem comentários. 

Estrutura JSON que você deve produzir obrigatoriamente:
{
  "header": {
    "tribunal": "O tribunal ou vara competente (ex: 2ª Vara Cível da Comarca de São Paulo)",
    "titulo": "Título da Ação Judicial (ex: Ação de Cobrança c/c Danos Morais)"
  },
  "autor": {
    "nome": "Nome completo do autor principal",
    "detalhes": "Qualificação simplificada (ex: CPF: 123.456.789-00, residente em São Paulo/SP)"
  },
  "reu": {
    "nome": "Nome completo do réu principal",
    "detalhes": "Qualificação simplificada (ex: CNPJ: 00.000.000/0001-00, sede em Rio de Janeiro/RJ)"
  },
  "kpis": [
    {
      "label": "Legenda curta (ex: Valor da Causa, Rito, Tutela Urgência, Multa Diária)",
      "value": "Conteúdo sucinto da legenda (ex: R$ 45.000,00, Comum, Sim, R$ 500,00)",
      "type": "Use uma das três opções exatas: 'default' (estilo neutro), 'alert' (para destacar riscos/alertas importantes) ou 'success' (para pontos favoráveis/deferidos)"
    }
  ],
  "timeline": [
    {
      "data": "Data por extenso ou resumida (ex: 15/03/2023)",
      "evento": "Nome curto do acontecimento",
      "descricao": "Resumo objetivo e rápido do fato histórico correspondente"
    }
  ],
  "fundamentos": [
    {
      "base_legal": "Artigo, lei ou jurisprudência base (ex: Art. 300, CPC)",
      "tema": "Tema da fundamentação jurídica (ex: Perigo de Dano)",
      "descricao": "Explicação sintética da tese do autor para este ponto"
    }
  ],
  "pedidos": [
    {
      "item": "Letra ou número identificador (ex: A, B, C)",
      "descricao": "Descrição direta da verba ou provimento solicitado",
      "valor": "Valor quantificado (ex: R$ 10.000,00 ou 'Não quantificável/obrigação de fazer')"
    }
  ],
  "footer": {
    "cidade": "Cidade de assinatura da petição",
    "data": "Data por extenso da petição",
    "advogado": "Nome do advogado responsável",
    "oab": "Número de inscrição da OAB e Estado correspondente"
  },
  "valor_total": "Valor total atribuído à causa (ou a soma total de pedidos monetários. Ex: R$ 50.000,00)"
}

Se faltar alguma informação no texto fornecido, preencha o respectivo campo com strings vazias ("") ou arrays vazios ([]).

REGRAS IMPORTANTES DE PERFORMANCE E CONCISÃO:
1. Caso você utilize uma etapa de raciocínio (bloco <think>...</think>), seja extremamente breve, sintético e direto. Limite o pensamento a no máximo 4 ou 5 linhas de texto. Não repita partes da petição inicial nem redija teses completas.
2. Foque em gerar a resposta em JSON o mais rápido e objetivamente possível.
PROMPT;

// Prepara requisição Ollama
$url = "$ollama_host/api/chat";
$data = [
    "model" => $model,
    "messages" => [
        ["role" => "system", "content" => $system_prompt],
        ["role" => "user", "content" => $petition_text]
    ],
    "stream" => true, // Habilita o streaming no Ollama
    "options" => [
        "num_gpu" => 99,
        "temperature" => 0.1
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
curl_setopt($ch, CURLOPT_TIMEOUT, 600); // Aumenta o tempo limite cURL para 10 minutos para suportar gerações longas sem abortar

// Buffer e lógica para streaming
$buffer = '';
$full_content = '';
$is_thinking = false;

curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$buffer, &$full_content, &$is_thinking) {
    $buffer .= $data;
    
    while (($pos = strpos($buffer, "\n")) !== false) {
        $line = substr($buffer, 0, $pos);
        $buffer = substr($buffer, $pos + 1);
        
        $line = trim($line);
        if (empty($line)) continue;
        
        $decoded = json_decode($line, true);
        if (!$decoded) continue;
        
        $chunk = $decoded['message']['content'] ?? '';
        if ($chunk === '') continue;
        
        $full_content .= $chunk;
        
        // Verifica transição do <think>
        if (strpos($chunk, '<think>') !== false) {
            $is_thinking = true;
            $chunk = str_replace('<think>', '', $chunk);
        }
        
        if (strpos($chunk, '</think>') !== false) {
            $is_thinking = false;
            $chunk = str_replace('</think>', '', $chunk);
            
            // Envia evento indicando fim do pensamento
            echo "event: think_end\n";
            echo "data: {}\n\n";
            ob_flush();
            flush();
        }
        
        if ($is_thinking) {
            echo "event: think\n";
            echo "data: " . json_encode(["text" => $chunk], JSON_UNESCAPED_UNICODE) . "\n\n";
        } else {
            echo "event: content\n";
            echo "data: " . json_encode(["text" => $chunk], JSON_UNESCAPED_UNICODE) . "\n\n";
        }
        
        ob_flush();
        flush();
    }
    return strlen($data);
});

// Executa cURL
$resultado = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_err = curl_error($ch);
curl_close($ch);

// Registra log para depuração local
file_put_contents(__DIR__ . '/debug_llm.log', "[" . date('Y-m-d H:i:s') . "] HTTP Code: $http_code | cURL Err: $curl_err | Full Content Size: " . strlen($full_content) . "\n\n", FILE_APPEND);

if ($resultado === false || $http_code !== 200) {
    echo "event: error\n";
    echo "data: " . json_encode([
        "error" => "Falha na comunicação com o LLM.",
        "details" => [
            "http_code" => $http_code,
            "curl_error" => $curl_err ?: 'Nenhum erro cURL registrado.'
        ]
    ]) . "\n\n";
    exit;
}

// Higienização e validação finais
function clean_json_string($str) {
    $first_brace = strpos($str, '{');
    $last_brace = strrpos($str, '}');
    if ($first_brace !== false && $last_brace !== false && $last_brace > $first_brace) {
        $str = substr($str, $first_brace, $last_brace - $first_brace + 1);
    }
    return trim($str);
}

$clean_json = clean_json_string($full_content);
$parsed_json = json_decode($clean_json, true);

if (!$parsed_json) {
    $parsed_json = [];
}

// --- ETAPA DE VALIDAÇÃO E SANITIZAÇÃO (Valores Padrão) ---

$sanitized = [];

// Header Validation
$sanitized['header'] = [
    'tribunal' => $parsed_json['header']['tribunal'] ?? 'Vara/Tribunal Correspondente',
    'titulo'   => $parsed_json['header']['titulo'] ?? 'Título do Pedido Judicial Principal'
];

// Autor Validation
$sanitized['autor'] = [
    'nome'     => $parsed_json['autor']['nome'] ?? 'Nome Completo do Autor',
    'detalhes' => $parsed_json['autor']['detalhes'] ?? 'Qualificação e detalhes'
];

// Reu Validation
$sanitized['reu'] = [
    'nome'     => $parsed_json['reu']['nome'] ?? 'Nome Completo do Réu',
    'detalhes' => $parsed_json['reu']['detalhes'] ?? 'Qualificação e detalhes'
];

// KPIs Validation
$sanitized['kpis'] = [];
if (isset($parsed_json['kpis']) && is_array($parsed_json['kpis'])) {
    foreach ($parsed_json['kpis'] as $kpi) {
        if (isset($kpi['label']) && isset($kpi['value'])) {
            $sanitized['kpis'][] = [
                'label' => $kpi['label'],
                'value' => $kpi['value'],
                'type'  => $kpi['type'] ?? 'default'
            ];
        }
    }
}

// Timeline Validation
$sanitized['timeline'] = [];
if (isset($parsed_json['timeline']) && is_array($parsed_json['timeline'])) {
    foreach ($parsed_json['timeline'] as $item) {
        if (isset($item['evento']) || isset($item['descricao'])) {
            $sanitized['timeline'][] = [
                'data'      => $item['data'] ?? 'Fato',
                'evento'    => $item['evento'] ?? 'Fato relevante',
                'descricao' => $item['descricao'] ?? ''
            ];
        }
    }
}

// Fundamentos Validation
$sanitized['fundamentos'] = [];
if (isset($parsed_json['fundamentos']) && is_array($parsed_json['fundamentos'])) {
    foreach ($parsed_json['fundamentos'] as $pillar) {
        if (isset($pillar['tema']) || isset($pillar['descricao'])) {
            $sanitized['fundamentos'][] = [
                'base_legal' => $pillar['base_legal'] ?? 'Direito',
                'tema'       => $pillar['tema'] ?? 'Tese',
                'descricao'  => $pillar['descricao'] ?? ''
            ];
        }
    }
}

// Pedidos Validation
$sanitized['pedidos'] = [];
if (isset($parsed_json['pedidos']) && is_array($parsed_json['pedidos'])) {
    foreach ($parsed_json['pedidos'] as $req) {
        if (isset($req['descricao'])) {
            $sanitized['pedidos'][] = [
                'item'      => $req['item'] ?? '',
                'descricao' => $req['descricao'],
                'valor'     => $req['valor'] ?? 'Não quantificado'
            ];
        }
    }
}

// Footer Validation
$sanitized['footer'] = [
    'cidade'   => $parsed_json['footer']['cidade'] ?? 'Não informada',
    'data'     => $parsed_json['footer']['data'] ?? date('d/m/Y'),
    'advogado' => $parsed_json['footer']['advogado'] ?? 'Nome do Advogado',
    'oab'      => $parsed_json['footer']['oab'] ?? 'OAB'
];

// Valor Total Validation
$sanitized['valor_total'] = $parsed_json['valor_total'] ?? 'Não informado';

// Retorna o JSON final sanitizado como evento SSE
echo "event: final_json\n";
echo "data: " . json_encode($sanitized, JSON_UNESCAPED_UNICODE) . "\n\n";
ob_flush();
flush();
?>
