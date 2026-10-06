<div class="remote-picker" data-kind="<?= e($pickerKind) ?>">
    <input type="hidden" id="<?= e($pickerId) ?>" name="<?= e($pickerName) ?>" value="<?= e($pickerValue) ?>" data-picker-value>
    <button type="button" id="<?= e($pickerId) ?>-trigger" class="picker-trigger" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-controls="<?= e($pickerId) ?>-options" aria-required="true"><span><?= e($pickerLabel?:'Selecione') ?></span><span aria-hidden="true">⌄</span></button>
    <div class="picker-panel" hidden>
        <input type="search" class="picker-search" placeholder="Digite para buscar…" aria-label="Buscar <?= e($pickerTitle) ?>" autocomplete="off" aria-controls="<?= e($pickerId) ?>-options">
        <p class="picker-status" role="status" aria-live="polite"></p>
        <div id="<?= e($pickerId) ?>-options" class="picker-results" role="listbox" aria-label="<?= e($pickerTitle) ?>"></div>
    </div>
</div>
