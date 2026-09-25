document.querySelectorAll('[data-confirm]').forEach(form => form.addEventListener('submit', event => { if (!window.confirm(form.dataset.confirm)) event.preventDefault(); }));
const filter = document.querySelector('#item-search');
filter?.addEventListener('input', () => { const term = filter.value.toLocaleLowerCase('pt-BR'); document.querySelectorAll('.item-option').forEach(row => { row.hidden = !row.textContent.toLocaleLowerCase('pt-BR').includes(term); }); });
