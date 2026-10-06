<?php
$currentModel=(int)($record['modelo_id']??0);
$modelInfo=$currentModel?query('SELECT marca_id,tipo_equipamento_id FROM modelos WHERE id=?',[$currentModel])->fetch():null;
$brand=(int)($record['inventario_marca_id']??$modelInfo['marca_id']??0);
$type=(int)($record['inventario_tipo_id']??$modelInfo['tipo_equipamento_id']??0);
$catalog=$brand?inventory_models($brand,$currentModel):[];
$types=[]; foreach($catalog as $model) $types[$model['tipo_equipamento_id']]=$model['tipo'];
asort($types,SORT_NATURAL|SORT_FLAG_CASE);
?>
<label for="inventario_marca_id">Marca *<select id="inventario_marca_id" name="inventario_marca_id" required><option value="">Selecione a marca</option><?php foreach(options('marcas',$brand) as $opt): ?><option value="<?= $opt['id'] ?>" <?= $brand===(int)$opt['id']?'selected':'' ?>><?= e($opt['label']) ?></option><?php endforeach ?></select></label>
<label for="inventario_tipo_id">Tipo *<select id="inventario_tipo_id" name="inventario_tipo_id" required <?= !$brand?'disabled':'' ?>><option value="">Selecione o tipo ou escolha um modelo</option><?php foreach($types as $typeId=>$typeName): ?><option value="<?= $typeId ?>" <?= $type===$typeId?'selected':'' ?>><?= e($typeName) ?></option><?php endforeach ?></select></label>
<label for="modelo_id">Modelo *<select id="modelo_id" name="modelo_id" required <?= !$brand?'disabled':'' ?>><option value="">Selecione o modelo</option><?php foreach($catalog as $model): if($type && $type!==(int)$model['tipo_equipamento_id']) continue; ?><option value="<?= $model['id'] ?>" <?= $currentModel===(int)$model['id']?'selected':'' ?>><?= e($model['label']) ?></option><?php endforeach ?></select><small>Após a marca, escolha o tipo para filtrar os modelos ou escolha o modelo para preencher o tipo.</small><p id="inventory-status" class="field-status" role="status" aria-live="polite"></p></label>
<script type="application/json" id="inventory-catalog"><?= json_encode($catalog,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?></script>
