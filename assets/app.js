document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));

async function readJson(url, options = {}) {
    const response = await fetch(url, { credentials: 'same-origin', cache: 'no-store', ...options });
    if (response.redirected) throw new Error('Sua sessão expirou. Recarregue a página.');
    let data;
    try { data = await response.json(); }
    catch { throw new Error('Não foi possível consultar os registros. Recarregue a página e tente novamente.'); }
    if (!response.ok) throw new Error(data.error || 'Não foi possível concluir a operação. Tente novamente.');
    return data;
}

function pickerUrl(module, loan = 0) {
    const url = new URL('app.php', window.location.href);
    url.search = new URLSearchParams({ module, action: 'picker', id: loan });
    return url;
}

const inventoryBrand = document.querySelector('#inventario_marca_id');
if (inventoryBrand) {
    const type = document.querySelector('#inventario_tipo_id');
    const model = document.querySelector('#modelo_id');
    const status = document.querySelector('#inventory-status');
    const form = inventoryBrand.form;
    let catalog = JSON.parse(document.querySelector('#inventory-catalog').textContent);
    let controller;
    let loading = false;
    function fill(select, rows, placeholder, current = '') {
        select.replaceChildren(new Option(placeholder, ''), ...rows.map(row => new Option(row.label, String(row.id))));
        select.value = String(current);
        if (!select.value) select.selectedIndex = 0;
    }
    function fillModels(current = '') {
        fill(model, catalog.filter(row => !type.value || String(row.tipo_equipamento_id) === type.value), 'Selecione o modelo', current);
    }
    inventoryBrand.addEventListener('change', async () => {
        controller?.abort();
        const request = controller = new AbortController();
        catalog = [];
        fill(type, [], 'Selecione o tipo ou escolha um modelo');
        fillModels();
        type.disabled = model.disabled = true;
        status.textContent = '';
        loading = Boolean(inventoryBrand.value);
        if (!loading) return;
        status.textContent = 'Carregando tipos e modelos…';
        try {
            const url = pickerUrl('inventario');
            url.searchParams.set('kind', 'inventory_models');
            url.searchParams.set('brand', inventoryBrand.value);
            const data = await readJson(url, { signal: request.signal });
            if (controller !== request) return;
            catalog = data.options;
            const types = new Map(catalog.map(row => [String(row.tipo_equipamento_id), { id: row.tipo_equipamento_id, label: row.tipo }]));
            fill(type, [...types.values()].sort((a, b) => a.label.localeCompare(b.label, 'pt-BR')), 'Selecione o tipo ou escolha um modelo');
            fillModels();
            type.disabled = model.disabled = false;
            status.textContent = catalog.length ? '' : 'Esta marca ainda não tem modelos ativos. Cadastre um modelo para continuar.';
        } catch (error) {
            if (error.name !== 'AbortError' && controller === request) status.textContent = error.message;
        } finally { if (controller === request) loading = false; }
    });
    type.addEventListener('change', () => { fillModels(model.value); });
    model.addEventListener('change', () => {
        const selected = catalog.find(row => String(row.id) === model.value);
        if (selected) { type.value = String(selected.tipo_equipamento_id); fillModels(selected.id); }
    });
    form.addEventListener('submit', event => {
        if (loading || !model.value || !type.value) {
            event.preventDefault();
            status.textContent = loading ? 'Aguarde o carregamento dos modelos.' : 'Selecione marca, tipo e modelo para continuar.';
            (inventoryBrand.value ? model : inventoryBrand).focus();
        }
    });
}

class RemotePicker {
    constructor(element, context) {
        this.element = element;
        this.context = context;
        this.valueInput = element.querySelector('[data-picker-value]');
        this.trigger = element.querySelector('.picker-trigger');
        this.panel = element.querySelector('.picker-panel');
        this.search = element.querySelector('.picker-search');
        this.results = element.querySelector('.picker-results');
        this.status = element.querySelector('.picker-status');
        this.generation = 0;
        this.trigger.addEventListener('click', event => {
            event.preventDefault();
            event.stopPropagation();
            this.panel.hidden ? this.open() : this.close();
        });
        this.element.dataset.pickerReady = 'true';
        this.search.addEventListener('input', event => {
            clearTimeout(this.timer);
            this.controller?.abort();
            this.generation++;
            this.results.replaceChildren();
            this.status.textContent = 'Aguardando busca…';
            if (!event.isComposing) this.timer = setTimeout(() => this.load(), 500);
        });
        this.search.addEventListener('compositionend', () => { clearTimeout(this.timer); this.timer = setTimeout(() => this.load(), 500); });
        this.element.addEventListener('keydown', event => {
            if (event.key === 'Escape') { event.preventDefault(); this.close(); this.trigger.focus(); }
            if (event.key === 'ArrowDown' && event.target === this.trigger) { event.preventDefault(); this.open(); }
            const buttons = [...this.results.querySelectorAll('button:not(:disabled)')];
            if (event.key === 'Enter' && event.target === this.search) {
                event.preventDefault();
                buttons[0]?.click();
            }
            if (!buttons.length) return;
            const index = buttons.indexOf(document.activeElement);
            if (event.key === 'ArrowDown' && event.target !== this.trigger) { event.preventDefault(); buttons[(index + 1) % buttons.length].focus(); }
            if (event.key === 'ArrowUp') { event.preventDefault(); buttons[(index - 1 + buttons.length) % buttons.length].focus(); }
            if (index >= 0 && ['Home', 'End'].includes(event.key)) { event.preventDefault(); buttons[event.key === 'Home' ? 0 : buttons.length - 1].focus(); }
        });
        this.outside = event => { if (!this.element.contains(event.target)) this.close(); };
        document.addEventListener('pointerdown', this.outside);
        this.element.addEventListener('focusout', event => {
            if (!this.selecting && event.relatedTarget && !this.element.contains(event.relatedTarget)) this.close();
        });
    }
    get value() { return this.valueInput.value; }
    setValue(option) {
        this.valueInput.value = option ? String(option.id) : '';
        this.trigger.firstElementChild.textContent = option?.label || 'Selecione';
        this.trigger.removeAttribute('aria-invalid');
    }
    open() {
        if (this.trigger.disabled) return;
        this.panel.hidden = false;
        this.trigger.setAttribute('aria-expanded', 'true');
        this.search.value = '';
        this.search.focus();
        this.load();
    }
    close() {
        clearTimeout(this.timer);
        this.controller?.abort();
        this.generation++;
        this.panel.hidden = true;
        this.trigger.setAttribute('aria-expanded', 'false');
    }
    async load() {
        if (this.panel.hidden) return;
        this.controller?.abort();
        this.controller = new AbortController();
        const generation = ++this.generation;
        this.results.replaceChildren();
        this.status.textContent = 'Carregando…';
        try {
            const url = pickerUrl('comodatos', this.context.loan);
            url.searchParams.set('kind', this.element.dataset.kind);
            url.searchParams.set('q', this.search.value);
            url.searchParams.set('reserva_token', this.context.token);
            for (const id of this.context.exclude?.(this) || []) url.searchParams.append('exclude[]', id);
            const data = await readJson(url, { signal: this.controller.signal });
            if (generation !== this.generation || this.panel.hidden) return;
            this.status.textContent = data.options.length ? 'Até 10 resultados. Digite para refinar a busca.' : 'Nenhum registro disponível para esta busca.';
            for (const option of data.options) {
                const button = document.createElement('button');
                button.type = 'button';
                button.tabIndex = -1;
                button.setAttribute('role', 'option');
                button.setAttribute('aria-selected', String(this.value === String(option.id)));
                button.textContent = option.label;
                button.addEventListener('click', async () => {
                    if (this.selecting) return;
                    this.selecting = true;
                    this.results.querySelectorAll('button').forEach(item => { item.disabled = true; });
                    this.status.textContent = this.element.dataset.kind === 'inventario' ? 'Reservando equipamento…' : 'Selecionando…';
                    try {
                        if (this.context.select) await this.context.select(this, option);
                        else this.setValue(option);
                        this.close();
                        this.trigger.focus();
                    } catch (error) {
                        this.status.textContent = error.message;
                        this.context.error?.(error);
                        if (!this.panel.hidden) this.search.focus();
                    }
                    finally { this.selecting = false; this.results.querySelectorAll('button').forEach(item => { item.disabled = false; }); }
                });
                this.results.append(button);
            }
        } catch (error) { if (error.name !== 'AbortError' && generation === this.generation) this.status.textContent = error.message; }
    }
    destroy() { this.close(); document.removeEventListener('pointerdown', this.outside); }
}

const equipmentSection = document.querySelector('.item-picker');
if (equipmentSection && equipmentSection.dataset.closed !== '1') {
    const form = equipmentSection.closest('form');
    const token = form.elements.reserva_token.value;
    const loan = equipmentSection.dataset.loanId;
    const csrf = form.elements.csrf.value;
    const rows = equipmentSection.querySelector('.equipment-rows');
    const status = document.querySelector('#reservation-status');
    const pickers = [];
    const equipmentPickers = [];
    let nextId = rows.children.length;
    let mutationTail = Promise.resolve();
    let readyToSubmit = false;
    let submitting = false;
    let pending = 0;
    let initializing = true;
    function queue(operation) {
        pending++;
        const result = mutationTail.then(operation);
        mutationTail = result.catch(() => {}).finally(() => { pending--; });
        return result;
    }
    async function mutate(operation, params = {}) {
        const body = new URLSearchParams({ operation, reserva_token: token, csrf });
        for (const [key, value] of Object.entries(params)) {
            if (Array.isArray(value)) value.forEach(item => body.append(`${key}[]`, item));
            else body.set(key, value);
        }
        return readJson(pickerUrl('comodatos', loan), { method: 'POST', body });
    }
    function selectedIds() { return equipmentPickers.filter(picker => picker.value).map(picker => picker.value); }
    function renumber() {
        [...rows.children].forEach((row, index) => {
            row.querySelector('label').textContent = `Equipamento ${index + 1} *`;
            row.querySelector('.remove-equipment').setAttribute('aria-label', `Remover equipamento ${index + 1}`);
        });
    }
    function setupRow(row) {
        const picker = new RemotePicker(row.querySelector('.remote-picker'), {
            token, loan,
            error: error => { status.textContent = error.message; },
            exclude: current => equipmentPickers.filter(other => other !== current && other.value).map(other => other.value),
            select: (current, option) => queue(async () => {
                if (submitting || !equipmentPickers.includes(current)) throw new Error('Aguarde a conclusão da operação atual.');
                if (equipmentPickers.some(other => other !== current && other.value === String(option.id))) throw new Error('Este equipamento já está selecionado neste comodato.');
                await mutate('reserve', { item: option.id, previous: current.value || 0 });
                current.setValue(option);
                status.textContent = '';
                equipmentPickers.filter(other => other !== current).forEach(other => other.close());
            })
        });
        equipmentPickers.push(picker);
        pickers.push(picker);
        row.querySelector('.remove-equipment').addEventListener('click', () => queue(async () => {
            if (submitting || !equipmentPickers.includes(picker)) return;
            const button = row.querySelector('.remove-equipment');
            button.disabled = true;
            try {
                if (picker.value) await mutate('release', { item: picker.value });
                if (equipmentPickers.length === 1) { picker.setValue(null); picker.close(); }
                else {
                    picker.destroy();
                    equipmentPickers.splice(equipmentPickers.indexOf(picker), 1);
                    pickers.splice(pickers.indexOf(picker), 1);
                    row.remove();
                }
                renumber();
                status.textContent = '';
            } finally { button.disabled = false; }
        }).catch(error => { status.textContent = error.message; }));
        return picker;
    }
    form.querySelectorAll('.form-grid .remote-picker').forEach(element => pickers.push(new RemotePicker(element, { token, loan })));
    [...rows.children].forEach(setupRow);
    equipmentSection.querySelector('.add-equipment').addEventListener('click', () => {
        const fragment = document.querySelector('#equipment-row-template').content.cloneNode(true);
        const row = fragment.firstElementChild;
        const id = `equipment-${nextId++}`;
        row.querySelector('[data-picker-value]').id = id;
        row.querySelector('label').htmlFor = `${id}-trigger`;
        const trigger = row.querySelector('.picker-trigger');
        trigger.id = `${id}-trigger`;
        trigger.setAttribute('aria-controls', `${id}-options`);
        row.querySelector('.picker-search').setAttribute('aria-controls', `${id}-options`);
        row.querySelector('.picker-results').id = `${id}-options`;
        rows.append(row);
        const picker = setupRow(row);
        renumber();
        picker.trigger.focus();
    });
    async function renew() {
        const ids = selectedIds();
        if (!ids.length) return [];
        const result = await mutate('renew', { itens: ids });
        for (const picker of equipmentPickers) {
            if (result.lost.includes(Number(picker.value))) { picker.setValue(null); picker.close(); }
        }
        if (result.lost.length) status.textContent = 'A reserva de um equipamento expirou ou ele ficou indisponível. Selecione-o novamente ou escolha outro item.';
        return result.lost;
    }
    queue(async () => {
        const selected = equipmentPickers.filter(picker => picker.value);
        if (selected.length) status.textContent = 'Confirmando disponibilidade dos equipamentos…';
        for (const picker of selected) {
            try { await mutate('reserve', { item: picker.value }); }
            catch (error) { picker.setValue(null); status.textContent = error.message; }
        }
        if (selected.every(picker => picker.value)) status.textContent = '';
    }).finally(() => { initializing = false; });
    const heartbeat = setInterval(() => {
        if (!submitting && !initializing) queue(renew).catch(error => { status.textContent = error.message; });
    }, 45000);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden && !submitting && !initializing) queue(renew).catch(error => { status.textContent = error.message; });
    });
    form.addEventListener('submit', event => {
        if (readyToSubmit) return;
        event.preventDefault();
        if (initializing || pending) { status.textContent = 'Aguarde a confirmação dos equipamentos antes de salvar.'; return; }
        const empty = pickers.find(picker => !picker.value);
        if (empty) {
            status.textContent = 'Preencha as seleções obrigatórias. Remova as linhas adicionais que não serão utilizadas.';
            empty.trigger.setAttribute('aria-invalid', 'true');
            empty.open();
            return;
        }
        queue(async () => {
            submitting = true;
            form.querySelector('fieldset').inert = true;
            status.textContent = 'Conferindo reservas…';
            const lost = await renew();
            if (lost.length) { submitting = false; form.querySelector('fieldset').inert = false; return; }
            readyToSubmit = true;
            submitting = true;
            form.requestSubmit(event.submitter);
        }).catch(error => { submitting = false; form.querySelector('fieldset').inert = false; status.textContent = error.message; });
    });
    window.addEventListener('pagehide', () => {
        clearInterval(heartbeat);
        if (!submitting) navigator.sendBeacon(pickerUrl('comodatos', loan), new URLSearchParams({ operation: 'release', reserva_token: token, csrf }));
    });
    window.addEventListener('pageshow', event => { if (event.persisted) window.location.reload(); });
}
