<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

auth_check();

$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';
$stats = dashboard_stats();
$recentProducts = db()->query('SELECT p.name, p.price, p.is_active, p.created_at, c.name AS collection_name FROM products p LEFT JOIN collections c ON c.id = p.collection_id ORDER BY p.created_at DESC LIMIT 5')->fetchAll();

require __DIR__ . '/includes/layout-header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Produtos</div>
        <div class="stat-value"><?= (int) $stats['products'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Ativos</div>
        <div class="stat-value"><?= (int) $stats['activeProducts'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Coleções</div>
        <div class="stat-value"><?= (int) $stats['collections'] ?></div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Usuários</div>
        <div class="stat-value"><?= (int) $stats['users'] ?></div>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Ações rápidas</h2>
    </div>
    <div class="table-actions">
        <a href="<?= e(admin_url('products/index.php?open=new')) ?>" class="btn btn-primary">Novo produto</a>
        <a href="<?= e(admin_url('collections/index.php?open=new')) ?>" class="btn btn-secondary">Nova coleção</a>
        <?php if (auth_is_root()): ?>
            <a href="<?= e(admin_url('users/index.php?open=new')) ?>" class="btn btn-outline">Novo usuário</a>
        <?php endif; ?>
    </div>
</div>

<div class="panel">
    <div class="panel-header">
        <h2 class="panel-title">Produtos recentes</h2>
        <a href="<?= e(admin_url('products/index.php')) ?>" class="btn btn-outline btn-sm">Ver todos</a>
    </div>

    <?php if (!$recentProducts): ?>
        <div class="empty-state">Nenhum produto cadastrado ainda.</div>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Coleção</th>
                    <th>Preço</th>
                    <th>Status</th>
                    <th>Criado em</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentProducts as $item): ?>
                    <tr>
                        <td><?= e($item['name']) ?></td>
                        <td><?= e($item['collection_name'] ?? '-') ?></td>
                        <td><?= e(format_money((float) $item['price'])) ?></td>
                        <td>
                            <span class="badge <?= $item['is_active'] ? 'badge-success' : 'badge-muted' ?>">
                                <?= $item['is_active'] ? 'Ativo' : 'Inativo' ?>
                            </span>
                        </td>
                        <td><?= e(format_date($item['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/layout-footer.php'; ?>
