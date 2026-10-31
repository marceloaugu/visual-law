<?php
$template_html = file_get_contents(__DIR__ . '/template.html');
$template_css = file_get_contents(__DIR__ . '/template.css');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visual Law AI Creator</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- PDF.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(20, 28, 48, 0.4);
            --border-color: rgba(59, 130, 246, 0.15);
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --accent-blue: #3b82f6;
            --accent-gold: #fbbf24;
            --glow-color: rgba(59, 130, 246, 0.25);
            --glass-blur: blur(12px);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 10% 20%, rgba(59, 130, 246, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 80%, rgba(251, 191, 36, 0.04) 0%, transparent 40%);
        }

        /* Top Header */
        header {
            padding: 20px 40px;
            background: rgba(11, 15, 25, 0.8);
            backdrop-filter: var(--glass-blur);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            font-size: 24px;
            color: var(--accent-blue);
            text-shadow: 0 0 10px var(--accent-blue);
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            background: linear-gradient(90deg, var(--text-primary) 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .logo-subtitle {
            font-size: 11px;
            color: var(--accent-gold);
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }

        .header-controls {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .model-selector {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .model-selector:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 10px var(--glow-color);
        }

        /* Workspace Grid */
        .workspace {
            flex-grow: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            height: calc(100vh - 82px);
        }

        .panel {
            padding: 25px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            height: 100%;
            overflow: hidden;
        }

        .panel-left {
            border-right: 1px solid var(--border-color);
        }

        .panel-right {
            background: rgba(15, 23, 42, 0.2);
        }

        /* Card Container */
        .card {
            background: var(--card-bg);
            backdrop-filter: var(--glass-blur);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
            transition: all 0.3s;
        }

        .card:hover {
            border-color: rgba(59, 130, 246, 0.3);
        }

        .card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title i {
            color: var(--accent-blue);
        }

        /* File Dropzone */
        .dropzone {
            border: 2px dashed rgba(59, 130, 246, 0.3);
            border-radius: 8px;
            padding: 25px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: rgba(15, 23, 42, 0.4);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .dropzone:hover, .dropzone.dragover {
            border-color: var(--accent-blue);
            background: rgba(59, 130, 246, 0.05);
            box-shadow: inset 0 0 10px rgba(59, 130, 246, 0.1);
        }

        .dropzone-icon {
            font-size: 32px;
            color: var(--accent-blue);
            transition: transform 0.3s;
        }

        .dropzone:hover .dropzone-icon {
            transform: translateY(-3px);
        }

        .dropzone p {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .dropzone span {
            color: var(--text-primary);
            font-weight: 600;
        }

        /* Textarea Editor */
        .editor-container {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 200px;
        }

        textarea {
            width: 100%;
            flex-grow: 1;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            padding: 15px;
            font-size: 13.5px;
            line-height: 1.6;
            resize: none;
            outline: none;
            transition: all 0.3s;
        }

        textarea:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 15px var(--glow-color);
        }

        /* Big Action Button */
        .btn-generate {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: white;
            padding: 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
            transition: all 0.3s;
        }

        .btn-generate:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(37, 99, 235, 0.45);
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .btn-generate:active {
            transform: translateY(0);
        }

        .btn-generate:disabled {
            background: #1e293b;
            color: #64748b;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Tab Switcher */
        .tabs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 10px;
        }

        .tab-btn-group {
            display: flex;
            gap: 8px;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-secondary);
            padding: 8px 16px;
            font-size: 13.5px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.3s;
        }

        .tab-btn:hover {
            color: var(--text-primary);
            background: rgba(255,255,255,0.02);
        }

        .tab-btn.active {
            color: var(--text-primary);
            background: rgba(59, 130, 246, 0.15);
            border: 1px solid rgba(59, 130, 246, 0.25);
        }

        /* Action Controls */
        .actions-group {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background: var(--accent-blue);
            border-color: var(--accent-blue);
            box-shadow: 0 0 10px var(--glow-color);
            transform: translateY(-1px);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        /* Frame/Viewer */
        .viewer-container {
            flex-grow: 1;
            position: relative;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.4);
            border: 1px solid rgba(255,255,255,0.05);
        }

        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: white;
        }

        .code-viewer {
            display: none;
            width: 100%;
            height: 100%;
            background: #0f1423;
            color: #38bdf8;
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            padding: 20px;
            border-radius: 12px;
            overflow: auto;
            white-space: pre-wrap;
            border: 1px solid var(--border-color);
        }

        /* Status & Loaders */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(11, 15, 25, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 15px;
            z-index: 10;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(59, 130, 246, 0.1);
            border-top: 4px solid var(--accent-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            box-shadow: 0 0 15px var(--glow-color);
        }

        .status-message {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Alert notifications */
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: rgba(16, 185, 129, 0.9);
            border: 1px solid rgba(16, 185, 129, 0.2);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 13.5px;
            display: none;
            gap: 8px;
            align-items: center;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
            z-index: 200;
            backdrop-filter: var(--glass-blur);
        }

        .toast.error {
            background: rgba(239, 68, 68, 0.9);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }
    </style>
</head>
<body>

    <header>
        <div class="logo-container">
            <i class="fa-solid fa-scale-balanced logo-icon"></i>
            <div>
                <h1>Visual Law AI Creator</h1>
                <div class="logo-subtitle">Laboratório de Inovação PPGDP</div>
            </div>
        </div>
        <div class="header-controls">
            <select id="model-select" class="model-selector">
                <option value="llama3:latest">Carregando modelos...</option>
            </select>
        </div>
    </header>

    <div class="workspace">
        <!-- PAINEL ESQUERDO: Entrada de Dados -->
        <div class="panel panel-left">
            <!-- Dropzone de Arquivos -->
            <div class="card">
                <div class="card-title">
                    <i class="fa-solid fa-file-pdf"></i>
                    Carregar Petição Judicial
                </div>
                <div id="dropzone" class="dropzone">
                    <i class="fa-solid fa-cloud-arrow-up dropzone-icon"></i>
                    <p>Arraste e solte sua petição em <span>PDF</span> ou <span>TXT</span> aqui</p>
                    <p style="font-size: 11px; color: var(--text-secondary);">Ou clique para selecionar de seus arquivos</p>
                    <input type="file" id="file-input" accept=".pdf,.txt" style="display: none;">
                </div>
            </div>

            <!-- Editor de Texto -->
            <div class="card editor-container">
                <div class="card-title" style="justify-content: space-between;">
                    <span><i class="fa-solid fa-paragraph"></i> Texto da Petição Bruta</span>
                    <span id="char-counter" style="font-size: 11px; color: var(--text-secondary);">0 caracteres</span>
                </div>
                <textarea id="petition-textarea" placeholder="Cole o texto completo da petição jurídica aqui para começarmos a estruturação..."></textarea>
                
                <button id="btn-generate" class="btn-generate" disabled>
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    Gerar Visual Law
                </button>
            </div>
        </div>

        <!-- PAINEL DIREITO: Visual Law Live Preview -->
        <div class="panel panel-right">
            <div class="tabs-header">
                <div class="tab-btn-group">
                    <button class="tab-btn active" onclick="switchTab('preview')">
                        <i class="fa-solid fa-eye" style="margin-right: 5px;"></i> Visualização Viva
                    </button>
                    <button class="tab-btn" onclick="switchTab('code')">
                        <i class="fa-solid fa-code" style="margin-right: 5px;"></i> Código Fonte
                    </button>
                </div>
                
                <div class="actions-group">
                    <button class="action-btn" title="Copiar HTML" onclick="copyHTML()">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                    <button class="action-btn" title="Baixar HTML" onclick="downloadHTML()">
                        <i class="fa-solid fa-download"></i>
                    </button>
                    <button class="action-btn" title="Imprimir / PDF" onclick="printHTML()">
                        <i class="fa-solid fa-print"></i>
                    </button>
                </div>
            </div>

            <div class="viewer-container">
                <!-- Overlay de Carregamento -->
                <div id="loading-overlay" class="loading-overlay">
                    <div class="spinner"></div>
                    <div id="status-message" class="status-message">Processando com inteligência artificial...</div>
                    <div id="thinking-container" style="display:none; max-width: 85%; max-height: 250px; overflow-y: auto; background: rgba(15, 23, 42, 0.75); border: 1px solid rgba(59, 130, 246, 0.25); border-radius: 8px; padding: 15px; font-family: 'Courier New', Courier, monospace; font-size: 12.5px; color: #a5b4fc; text-align: left; white-space: pre-wrap; line-height: 1.5; margin-top: 15px; box-shadow: inset 0 0 15px rgba(59, 130, 246, 0.05);"></div>
                </div>

                <!-- Iframe do Preview -->
                <iframe id="preview-iframe"></iframe>

                <!-- Editor de Código -->
                <textarea id="code-viewer" class="code-viewer" readonly></textarea>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <i class="fa-solid fa-circle-check"></i>
        <span id="toast-text">Copiado com sucesso!</span>
    </div>

    <!-- Link do Script do Renderizador -->
    <script src="renderer.js"></script>

    <script>
        // Injeta os templates lidos do servidor para serem consumidos localmente pelo renderer.js
        const templateHtml = <?php echo json_encode($template_html); ?>;
        const templateCss = <?php echo json_encode($template_css); ?>;

        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('file-input');
        const textarea = document.getElementById('petition-textarea');
        const btnGenerate = document.getElementById('btn-generate');
        const charCounter = document.getElementById('char-counter');
        const modelSelect = document.getElementById('model-select');
        const loadingOverlay = document.getElementById('loading-overlay');
        const statusMessage = document.getElementById('status-message');
        const previewIframe = document.getElementById('preview-iframe');
        const codeViewer = document.getElementById('code-viewer');
        
        let generatedHTML = "";

        const TARGET_MODELS = [
            { name: 'Llama 3 (8B)', id: 'llama3' },
            { name: 'Llama 3.1 (8B)', id: 'llama3.1' },
            { name: 'Llama 3.2 (1B)', id: 'llama3.2:1b' },
            { name: 'Llama 3.2 (3B)', id: 'llama3.2:3b' },
            { name: 'Phi-3 Mini (3.8B)', id: 'phi3:mini' },
            { name: 'Phi-3.5 Mini (3.8B)', id: 'phi3.5' },
            { name: 'Mistral v0.3 (7B)', id: 'mistral:v0.3' },
            { name: 'DeepSeek-R1 (1.5B)', id: 'deepseek-r1:1.5b' },
            { name: 'DeepSeek-R1 (7B)', id: 'deepseek-r1:7b' },
            { name: 'DeepSeek-R1 (8B)', id: 'deepseek-r1:8b' },
            { name: 'Gemma 2 (2B)', id: 'gemma2:2b' },
            { name: 'Gemma 2 (9B)', id: 'gemma2:9b' },
            { name: 'Qwen 2.5 (7B)', id: 'qwen2.5:7b' }
        ];

        let installedModelsGlobal = [];

        // Carrega os modelos instalados no Ollama via api_visual.php
        async function loadModels() {
            try {
                const [modelsRes, runningRes] = await Promise.all([
                    fetch('api_visual.php?action=list_models'),
                    fetch('api_visual.php?action=list_running_models')
                ]);
                const data = await modelsRes.json();
                const runningData = await runningRes.json();
                
                modelSelect.innerHTML = '';
                
                const installedModels = (data.models || []).map(m => m.name);
                const runningModels = (runningData.models || []).map(m => m.name);
                
                installedModelsGlobal = installedModels;

                const checkInstalled = (modelId) => {
                    const cleanTarget = modelId.replace(/:latest$/, '');
                    return installedModels.some(m => m.replace(/:latest$/, '') === cleanTarget);
                };

                const checkRunning = (modelId) => {
                    const cleanTarget = modelId.replace(/:latest$/, '');
                    return runningModels.some(m => m.replace(/:latest$/, '') === cleanTarget);
                };

                // Add Target Models
                const targetGroup = document.createElement('optgroup');
                targetGroup.label = "Modelos Sugeridos";
                
                TARGET_MODELS.forEach(target => {
                    const opt = document.createElement('option');
                    const exactInstalledName = (data.models || []).find(m => m.name.replace(/:latest$/, '') === target.id.replace(/:latest$/, ''));
                    const modelValue = exactInstalledName ? exactInstalledName.name : target.id;
                    opt.value = modelValue;
                    
                    const isInstalled = checkInstalled(target.id);
                    const isRunning = checkRunning(target.id);
                    
                    if (isRunning) {
                        opt.innerText = `${target.name} 🚀 (Rodando / RAM)`;
                        opt.style.fontWeight = 'bold';
                        opt.style.color = '#3fb950';
                    } else {
                        opt.innerText = `${target.name} ${isInstalled ? '✅ (Instalado)' : '⏳ (Pull necessário)'}`;
                        if (!isInstalled) opt.style.color = '#888';
                    }
                    targetGroup.appendChild(opt);
                });
                modelSelect.appendChild(targetGroup);

                // Add other installed models not in target list
                const otherModels = installedModels.filter(m => !TARGET_MODELS.some(t => t.id.replace(/:latest$/, '') === m.replace(/:latest$/, '')));
                if (otherModels.length > 0) {
                    const otherGroup = document.createElement('optgroup');
                    otherGroup.label = "Outros Modelos Instalados";
                    otherModels.forEach(m => {
                        const opt = document.createElement('option');
                        opt.value = m;
                        const isRunning = checkRunning(m);
                        if (isRunning) {
                            opt.innerText = `${m} 🚀 (Rodando / RAM)`;
                            opt.style.fontWeight = 'bold';
                            opt.style.color = '#3fb950';
                        } else {
                            opt.innerText = `${m} ✅`;
                        }
                        otherGroup.appendChild(opt);
                    });
                    modelSelect.appendChild(otherGroup);
                }

                // Filtra apenas modelos reais de chat instalados
                const chatCapableInstalledModels = installedModels.filter(m => !m.includes('embed'));

                // Restore last selected model
                const lastModel = localStorage.getItem('selectedModel');
                if (lastModel && Array.from(modelSelect.options).some(o => o.value === lastModel)) {
                    modelSelect.value = lastModel;
                } else if (chatCapableInstalledModels.length > 0) {
                    const firstInstalled = TARGET_MODELS.find(t => checkInstalled(t.id));
                    if (firstInstalled) {
                        const exactInstalledName = (data.models || []).find(m => m.name.replace(/:latest$/, '') === firstInstalled.id.replace(/:latest$/, ''));
                        modelSelect.value = exactInstalledName ? exactInstalledName.name : firstInstalled.id;
                    } else {
                        modelSelect.value = chatCapableInstalledModels[0];
                    }
                }
            } catch (e) {
                console.error("Erro ao buscar modelos:", e);
                modelSelect.innerHTML = '<option value="llama3:latest">Llama 3 (Padrão)</option>';
            }
        }

        loadModels();

        // Escuta mudança de modelo e ativa na VRAM descarregando outros
        modelSelect.addEventListener('change', async (e) => {
            const selectedModel = e.target.value;
            
            const normalize = (name) => name.toLowerCase().trim().replace(/:latest$/, '');
            const isInstalled = installedModelsGlobal.some(m => normalize(m) === normalize(selectedModel));
            
            if (!isInstalled) {
                const target = TARGET_MODELS.find(t => normalize(t.id) === normalize(selectedModel));
                const friendlyName = target ? target.name : selectedModel;
                
                alert(`⚠️ O modelo "${friendlyName}" precisa ser instalado primeiro.\nExecute no terminal: ollama pull ${selectedModel}`);
                
                const lastModel = localStorage.getItem('selectedModel');
                if (lastModel) {
                    e.target.value = lastModel;
                } else {
                    loadModels();
                }
                return;
            }
            
            const select = e.target;
            
            // Bloquear UI temporariamente
            select.disabled = true;
            btnGenerate.disabled = true;
            
            showToast(`🚀 Inicializando ${selectedModel}... Liberando VRAM de outros modelos...`, false);
            
            try {
                const formData = new FormData();
                formData.append('model', selectedModel);
                
                const res = await fetch('api_visual.php?action=run_model', {
                    method: 'POST',
                    body: formData
                });
                const result = await res.json();
                
                if (result.success) {
                    localStorage.setItem('selectedModel', selectedModel);
                    showToast(`✨ Modelo ${selectedModel} carregado com sucesso!`, false);
                    setTimeout(loadModels, 1000);
                } else {
                    throw new Error(result.error || "Erro desconhecido");
                }
            } catch (err) {
                console.error("Erro ao carregar modelo:", err);
                showToast(`❌ Falha ao carregar o modelo ${selectedModel}. Verifique o Ollama.`, true);
                const lastModel = localStorage.getItem('selectedModel');
                if (lastModel) select.value = lastModel;
            } finally {
                select.disabled = false;
                updateTextState(); // Habilita o botão gerar se o texto não estiver vazio
            }
        });

        // Controladores do Dropzone
        dropzone.onclick = () => fileInput.click();
        
        dropzone.ondragover = (e) => {
            e.preventDefault();
            dropzone.classList.add('dragover');
        };

        dropzone.ondragleave = () => {
            dropzone.classList.remove('dragover');
        };

        dropzone.ondrop = async (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                handleFile(e.dataTransfer.files[0]);
            }
        };

        fileInput.onchange = (e) => {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        };

        // Tratamento do Arquivo (PDF ou TXT)
        async function handleFile(file) {
            showToast("Lendo arquivo...", false);
            try {
                if (file.type === "application/pdf" || file.name.endsWith('.pdf')) {
                    const text = await extractTextFromPDF(file);
                    textarea.value = text;
                } else {
                    const text = await file.text();
                    textarea.value = text;
                }
                updateTextState();
                showToast("Arquivo importado com sucesso!", false);
            } catch (err) {
                console.error("Erro ao ler o arquivo:", err);
                showToast("Falha ao ler o arquivo. Verifique se o PDF possui texto.", true);
            }
        }

        // Extrai texto de PDF usando PDF.js
        async function extractTextFromPDF(file) {
            const reader = new FileReader();
            return new Promise((resolve, reject) => {
                reader.onload = async function() {
                    try {
                        const typedarray = new Uint8Array(this.result);
                        const pdf = await pdfjsLib.getDocument(typedarray).promise;
                        let fullText = "";
                        for (let i = 1; i <= pdf.numPages; i++) {
                            const page = await pdf.getPage(i);
                            const textContent = await page.getTextContent();
                            const pageText = textContent.items.map(item => item.str).join(' ');
                            fullText += pageText + "\n\n";
                        }
                        resolve(fullText);
                    } catch (err) {
                        reject(err);
                    }
                };
                reader.onerror = reject;
                reader.readAsArrayBuffer(file);
            });
        }

        // Monitora o estado da caixa de texto
        textarea.oninput = () => updateTextState();

        function updateTextState() {
            const text = textarea.value.trim();
            charCounter.innerText = `${text.length} caracteres`;
            btnGenerate.disabled = text.length === 0;
        }

        // --- GERAÇÃO VISUAL LAW ---
        btnGenerate.onclick = async () => {
            const prompt = textarea.value.trim();
            if (!prompt) return;

            const thinkingContainer = document.getElementById('thinking-container');

            // Inicia estados de carregamento
            loadingOverlay.style.display = 'flex';
            statusMessage.innerText = "Verificando se o modelo selecionado está ativo...";
            thinkingContainer.style.display = 'none';
            thinkingContainer.textContent = '';
            btnGenerate.disabled = true;
            
            generatedHTML = "";
            updateIframe("");
            codeViewer.value = "";

            try {
                // Garantir preventivamente que o modelo está ativo antes da chamada de geração
                const runningRes = await fetch('api_visual.php?action=list_running_models');
                const runningData = await runningRes.json();
                const runningModels = (runningData.models || []).map(m => m.name.toLowerCase().trim().replace(/:latest$/, ''));
                const selectedModel = modelSelect.value;
                const selectedModelNorm = selectedModel.toLowerCase().trim().replace(/:latest$/, '');
                
                if (!runningModels.includes(selectedModelNorm)) {
                    statusMessage.innerText = `Inicializando ${selectedModel} na GPU/RAM...`;
                    const formData = new FormData();
                    formData.append('model', selectedModel);
                    await fetch('api_visual.php?action=run_model', {
                        method: 'POST',
                        body: formData
                    });
                    
                    await loadModels();
                }
                
                statusMessage.innerText = "Analisando a petição...";
                
                const response = await fetch('api_visual.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `prompt=${encodeURIComponent(prompt)}&model=${encodeURIComponent(modelSelect.value)}`
                });

                if (!response.ok) throw new Error("Erro de conexão com o servidor de Visual Law");

                const reader = response.body.getReader();
                const decoder = new TextDecoder();
                let buffer = '';
                let jsonResponse = null;

                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop(); // Guarda a última linha incompleta

                    let currentEvent = '';
                    for (let i = 0; i < lines.length; i++) {
                        const line = lines[i].trim();
                        if (line.startsWith('event:')) {
                            currentEvent = line.replace('event:', '').trim();
                        } else if (line.startsWith('data:')) {
                            const dataStr = line.replace('data:', '').trim();
                            try {
                                const parsedData = JSON.parse(dataStr);
                                if (currentEvent === 'think') {
                                    thinkingContainer.style.display = 'block';
                                    thinkingContainer.textContent += parsedData.text;
                                    thinkingContainer.scrollTop = thinkingContainer.scrollHeight;
                                    statusMessage.innerText = "Inteligência Artificial Pensando...";
                                } else if (currentEvent === 'think_end') {
                                    statusMessage.innerText = "Estruturando dados finais em JSON...";
                                } else if (currentEvent === 'final_json') {
                                    jsonResponse = parsedData;
                                } else if (currentEvent === 'error') {
                                    if (parsedData.details) {
                                        throw new Error(parsedData.error + ' (cURL: ' + parsedData.details.curl_error + ', HTTP: ' + parsedData.details.http_code + ')');
                                    }
                                    throw new Error(parsedData.error);
                                }
                            } catch (e) {
                                if (currentEvent === 'error') {
                                    throw e;
                                }
                            }
                        }
                    }
                }

                if (!jsonResponse) {
                    throw new Error("Não foi possível obter dados estruturados válidos do modelo.");
                }

                if (jsonResponse.error) {
                    if (jsonResponse.details) {
                        console.error("LLM Error Details:", jsonResponse.details);
                        throw new Error(`${jsonResponse.error} (cURL: ${jsonResponse.details.curl_error}, HTTP: ${jsonResponse.details.http_code})`);
                    }
                    throw new Error(jsonResponse.error);
                }

                statusMessage.innerText = "Construindo elementos do Visual Law...";

                // Renderiza o HTML final dinamicamente usando renderer.js localmente
                const renderedHtml = renderVisualLaw(jsonResponse, templateHtml, templateCss);

                generatedHTML = renderedHtml;
                updateIframe(renderedHtml);
                codeViewer.value = renderedHtml;
                
                showToast("Visual Law gerado com sucesso!", false);
            } catch (err) {
                console.error("Erro na geração:", err);
                if (!generatedHTML) {
                    loadInitialTemplate();
                }
                showToast("Ocorreu um erro na geração: " + err.message, true);
            } finally {
                loadingOverlay.style.display = 'none';
                btnGenerate.disabled = false;
            }
        };

        // Atualiza a visualização do iframe dinamicamente
        function updateIframe(htmlContent) {
            const doc = previewIframe.contentDocument || previewIframe.contentWindow.document;
            doc.open();
            doc.write(htmlContent);
            doc.close();
        }

        // Carrega o template inicial no preview ao abrir a aplicação
        function loadInitialTemplate() {
            const initialHtml = renderVisualLaw({}, templateHtml, templateCss);
            generatedHTML = initialHtml;
            updateIframe(initialHtml);
            codeViewer.value = initialHtml;
        }

        loadInitialTemplate();

        // Troca de Abas
        function switchTab(tab) {
            const tabs = document.querySelectorAll('.tab-btn');
            tabs.forEach(btn => btn.classList.remove('active'));

            if (tab === 'preview') {
                document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
                previewIframe.style.display = 'block';
                codeViewer.style.display = 'none';
            } else {
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
                previewIframe.style.display = 'none';
                codeViewer.style.display = 'block';
            }
        }

        // --- BOTÕES DE AÇÃO ---
        function copyHTML() {
            if (!generatedHTML) {
                showToast("Nenhum código gerado ainda.", true);
                return;
            }
            navigator.clipboard.writeText(generatedHTML);
            showToast("Código HTML copiado!", false);
        }

        // Realiza o download do arquivo HTML final
        function downloadHTML() {
            if (!generatedHTML) {
                showToast("Nenhum código gerado para download.", true);
                return;
            }
            const blob = new Blob([generatedHTML], { type: 'text/html;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.setAttribute("download", "visual_law_peticao.html");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showToast("Download iniciado!", false);
        }

        // Abre caixa de diálogo de impressão do Iframe
        function printHTML() {
            if (!previewIframe.srcdoc && !previewIframe.contentWindow.document.body.innerHTML) {
                showToast("Nenhum documento para imprimir.", true);
                return;
            }
            previewIframe.contentWindow.focus();
            previewIframe.contentWindow.print();
        }

        // Mostrar Toast feedback
        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            const toastText = document.getElementById('toast-text');
            
            toastText.innerText = message;
            if (isError) {
                toast.classList.add('error');
                toast.querySelector('i').className = "fa-solid fa-circle-exclamation";
            } else {
                toast.classList.remove('error');
                toast.querySelector('i').className = "fa-solid fa-circle-check";
            }
            
            toast.style.display = 'flex';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    </script>
</body>
</html>
