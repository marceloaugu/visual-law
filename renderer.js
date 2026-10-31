/**
 * Dynamic Renderer for Visual Law Documents
 * Takes parsed JSON, template HTML, and CSS content and builds the final document.
 */

function renderVisualLaw(jsonData, templateHtml, cssContent) {
    // 1. Parse templateHtml
    const parser = new DOMParser();
    const doc = parser.parseFromString(templateHtml, 'text/html');

    // Helper to safely set text content of elements
    const setElText = (selector, val, defaultVal = '') => {
        const el = doc.querySelector(selector);
        if (el) {
            el.textContent = (val !== undefined && val !== null && val !== '') ? val : defaultVal;
        }
    };

    // Helper to escape HTML for safe string insertion
    const escapeHtml = (text) => {
        if (text === undefined || text === null) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    };

    // 2. Populate basic metadata fields
    const header = jsonData.header || {};
    setElText('#header-tribunal', header.tribunal, 'Vara/Tribunal Correspondente');
    setElText('#header-titulo', header.titulo, 'Título do Pedido Judicial Principal');

    const autor = jsonData.autor || {};
    setElText('#autor-nome', autor.nome, 'Nome Completo do Autor');
    setElText('#autor-detalhes', autor.detalhes, 'Detalhes, CPF, endereço, cargo');

    const reu = jsonData.reu || {};
    setElText('#reu-nome', reu.nome, 'Nome Completo do Réu');
    setElText('#reu-detalhes', reu.detalhes, 'Qualificação, CNPJ/CPF, endereço');

    const footer = jsonData.footer || {};
    setElText('#footer-cidade', footer.cidade, 'Cidade');
    setElText('#footer-data', footer.data, 'Data');
    setElText('#footer-advogado', footer.advogado, 'Nome do Advogado');
    setElText('#footer-oab', footer.oab, 'OAB');

    // 3. Populate KPIs (Resumo do caso)
    const kpiContainer = doc.getElementById('kpi-container');
    if (kpiContainer) {
        kpiContainer.innerHTML = '';
        const kpis = Array.isArray(jsonData.kpis) ? jsonData.kpis : [];
        
        if (kpis.length === 0) {
            // Default fallbacks if no KPIs present
            kpiContainer.innerHTML = `
                <td class="kpi-card" style="width: 33.33%;">
                    <div class="kpi-label">Valor da Causa</div>
                    <div class="kpi-value">${escapeHtml(jsonData.valor_total || 'Não informado')}</div>
                </td>
                <td class="kpi-card alert" style="width: 33.33%;">
                    <div class="kpi-label">Rito Processual</div>
                    <div class="kpi-value">Comum</div>
                </td>
                <td class="kpi-card success" style="width: 33.33%;">
                    <div class="kpi-label">Pedido Liminar</div>
                    <div class="kpi-value">Sim</div>
                </td>
            `;
        } else {
            kpis.forEach(kpi => {
                const td = doc.createElement('td');
                td.className = 'kpi-card';
                if (kpi.type === 'alert') {
                    td.classList.add('alert');
                } else if (kpi.type === 'success') {
                    td.classList.add('success');
                }
                td.style.width = (100 / kpis.length) + '%';
                
                const labelDiv = doc.createElement('div');
                labelDiv.className = 'kpi-label';
                labelDiv.textContent = kpi.label || '';
                
                const valueDiv = doc.createElement('div');
                valueDiv.className = 'kpi-value';
                valueDiv.textContent = kpi.value || '';
                
                td.appendChild(labelDiv);
                td.appendChild(valueDiv);
                kpiContainer.appendChild(td);
            });
        }
    }

    // 4. Populate Timeline (Linha do tempo)
    const timelineContainer = doc.getElementById('timeline-container');
    if (timelineContainer) {
        timelineContainer.innerHTML = '';
        const timeline = Array.isArray(jsonData.timeline) ? jsonData.timeline : [];
        
        if (timeline.length === 0) {
            timelineContainer.innerHTML = `
                <div class="timeline-item">
                    <div class="timeline-badge">Fato</div>
                    <div class="timeline-content">
                        <div class="timeline-title">Fatos Iniciais</div>
                        <div class="timeline-desc">Origem da demanda conforme petição inicial apresentada.</div>
                    </div>
                </div>
            `;
        } else {
            timeline.forEach(item => {
                const div = doc.createElement('div');
                div.className = 'timeline-item';

                const badge = doc.createElement('div');
                badge.className = 'timeline-badge';
                badge.textContent = item.data || 'Fato';

                const content = doc.createElement('div');
                content.className = 'timeline-content';

                const title = doc.createElement('div');
                title.className = 'timeline-title';
                title.textContent = item.evento || '';

                const desc = doc.createElement('div');
                desc.className = 'timeline-desc';
                desc.textContent = item.descricao || '';

                content.appendChild(title);
                content.appendChild(desc);
                div.appendChild(badge);
                div.appendChild(content);
                timelineContainer.appendChild(div);
            });
        }
    }

    // 5. Populate Pillars (Fundamentos Jurídicos)
    const pillarsContainer = doc.getElementById('pillars-container');
    if (pillarsContainer) {
        pillarsContainer.innerHTML = '';
        const fundamentos = Array.isArray(jsonData.fundamentos) ? jsonData.fundamentos : [];
        
        if (fundamentos.length === 0) {
            pillarsContainer.innerHTML = `
                <td class="pillar-box" style="width: 100%;">
                    <div class="pillar-tag">Direito</div>
                    <div class="pillar-title">Fundamentação Geral</div>
                    <div class="pillar-desc">Amparo legal e constitucional da pretensão deduzida em juízo.</div>
                </td>
            `;
        } else {
            fundamentos.forEach(pillar => {
                const td = doc.createElement('td');
                td.className = 'pillar-box';
                td.style.width = (100 / fundamentos.length) + '%';

                const tag = doc.createElement('div');
                tag.className = 'pillar-tag';
                tag.textContent = pillar.base_legal || 'Direito';

                const title = doc.createElement('div');
                title.className = 'pillar-title';
                title.textContent = pillar.tema || '';

                const desc = doc.createElement('div');
                desc.className = 'pillar-desc';
                desc.textContent = pillar.descricao || '';

                td.appendChild(tag);
                td.appendChild(title);
                td.appendChild(desc);
                pillarsContainer.appendChild(td);
            });
        }
    }

    // 6. Populate Requests (Quadro de Pedidos)
    const requestsContainer = doc.getElementById('requests-container');
    if (requestsContainer) {
        requestsContainer.innerHTML = '';
        const pedidos = Array.isArray(jsonData.pedidos) ? jsonData.pedidos : [];
        
        if (pedidos.length === 0) {
            const tr = doc.createElement('tr');
            tr.innerHTML = `
                <td><span class="badge-check">✓</span> A</td>
                <td>Pedido principal conforme petição inicial</td>
                <td style="text-align: right; font-weight: 600;">A apurar</td>
            `;
            requestsContainer.appendChild(tr);
        } else {
            pedidos.forEach(req => {
                const tr = doc.createElement('tr');

                const itemTd = doc.createElement('td');
                itemTd.innerHTML = `<span class="badge-check">✓</span> ${escapeHtml(req.item || '')}`;

                const descTd = doc.createElement('td');
                descTd.textContent = req.descricao || '';

                const valTd = doc.createElement('td');
                valTd.style.textAlign = 'right';
                valTd.style.fontWeight = '600';
                valTd.textContent = req.valor || '';

                tr.appendChild(itemTd);
                tr.appendChild(descTd);
                tr.appendChild(valTd);
                requestsContainer.appendChild(tr);
            });
        }

        // Add Total Row
        const totalTr = doc.createElement('tr');
        totalTr.className = 'row-total';

        const totalItemTd = doc.createElement('td');
        totalItemTd.textContent = 'Total';

        const totalDescTd = doc.createElement('td');
        totalDescTd.textContent = 'Valor Total e Líquido da Causa';

        const totalValTd = doc.createElement('td');
        totalValTd.style.textAlign = 'right';
        totalValTd.textContent = jsonData.valor_total || 'Não informado';

        totalTr.appendChild(totalItemTd);
        totalTr.appendChild(totalDescTd);
        totalTr.appendChild(totalValTd);
        requestsContainer.appendChild(totalTr);
    }

    // 7. Inject template.css contents into style tag
    const styleTag = doc.querySelector('head link[href="template.css"]');
    if (styleTag) {
        const style = doc.createElement('style');
        style.textContent = cssContent;
        styleTag.replaceWith(style);
    } else {
        const style = doc.createElement('style');
        style.textContent = cssContent;
        doc.head.appendChild(style);
    }

    return '<!DOCTYPE html>\n' + doc.documentElement.outerHTML;
}
