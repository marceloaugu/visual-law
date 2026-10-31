# ⚖️ Visual Law AI Creator

Este projeto é uma ferramenta avançada desenvolvida em **PHP** e **Modern JavaScript** integrada ao **Ollama** que extrai dados estruturados de petições jurídicas brutas (em formato PDF ou TXT) e renderiza documentos interativos e dinâmicos baseados nas melhores práticas de **Visual Law**.

---

## 🛠️ Nova Arquitetura Estável

A nova arquitetura elimina completamente o envio e a geração de código HTML/CSS pelo modelo de linguagem. O LLM é utilizado **exclusivamente** para processar e compreender o texto jurídico, respondendo em formato JSON estrito.

```
Entrada (PDF/TXT)
      ↓
Petição (Texto Bruto)
      ↓
Ollama (LLM Local)
      ↓
JSON Estruturado
      ↓
PHP (api_visual.php - Validação e Sanitização)
      ↓
JavaScript (renderer.js - Compilação Dinâmica)
      ↓
HTML Final (template.html + template.css)
      ↓
Preview Interativo (Iframe) / Download / Impressão
```

---

## 📁 Estrutura do Projeto

*   **`index.php`**: Interface frontend do usuário. Permite carregar documentos, selecionar modelos, visualizar o preview do Visual Law gerado, inspecionar o código fonte compilado, fazer download ou imprimir.
*   **`api_visual.php`**: Backend PHP que gerencia a comunicação estruturada com o Ollama, valida o JSON de saída de acordo com o esquema e preenche valores ausentes ou corrompidos de forma robusta.
*   **`renderer.js`**: Mecanismo de compilação dinâmica client-side. Transforma o JSON higienizado em elementos reais do DOM de forma segura.
*   **`template.html`**: Esqueleto estrutural em HTML do Visual Law contendo os placeholders e pontos de inserção dinâmica de dados.
*   **`template.css`**: Folha de estilos independente do documento gerado.
*   **`schema.json`**: Validador de esquema de dados para alinhar o JSON retornado pela inteligência artificial.

---

## 🚀 Como Executar

Configure os arquivos no diretório do seu servidor web com suporte a PHP e acesse a aplicação pelo navegador (ex: `/visual-law/`).

---

## 🧠 Modelos Recomendados (Ollama)

Os modelos são otimizados localmente utilizando a flag `"num_gpu" => 99` e `"format" => "json"` para garantir respostas estruturadas e extremamente rápidas.

Comandos para instalação via terminal:
*   `ollama pull llama3.2:3b` (Ideal para máquinas com pouca VRAM)
*   `ollama pull llama3.1` (Recomendado para melhor precisão e compreensão de textos complexos)
*   `ollama pull qwen2.5:7b` (Excelente desempenho em formatação e escrita estruturada)
*   `ollama pull deepseek-r1:8b` (Excelente raciocínio estruturado)
