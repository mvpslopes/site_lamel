<?php

declare(strict_types=1);

/** @var string $modalFormBase */
/** @var string $modalTitleNew */
/** @var string $modalTitleEdit */
?>
<div id="admin-form-modal" class="admin-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="admin-form-modal-title" data-form-base="<?= e($modalFormBase) ?>" data-title-new="<?= e($modalTitleNew) ?>" data-title-edit="<?= e($modalTitleEdit) ?>">
    <div class="admin-modal-backdrop" aria-hidden="true"></div>
    <div class="admin-modal-dialog">
        <div class="admin-modal-header">
            <h2 class="admin-modal-title" id="admin-form-modal-title">Cadastro</h2>
            <button type="button" class="admin-modal-close" data-modal-close aria-label="Fechar">&times;</button>
        </div>
        <div class="admin-modal-body" id="admin-form-modal-body">
            <div class="admin-modal-loading">Carregando formulário...</div>
        </div>
    </div>
</div>
