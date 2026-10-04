<?php
use yii\helpers\Url;
use yii\helpers\Html;
use mdm\admin\components\MenuHelper;

// Callback untuk menyesuaikan struktur data dari mdmsoft
$callback = function ($menu) {
    // Parsing data tambahan (misal class ikon disimpan di kolom data: {"icon": "iconoir-user"})
    $data = [];
    if (!empty($menu['data'])) {
        $decoded = @json_decode($menu['data'], true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $data = $decoded;
        } else {
            // Kompatibilitas jika disimpan format array eval/string
            $data = @eval('return ' . $menu['data'] . ';') ?: [];
        }
    }

    return [
        'id' => $menu['id'],
        'label' => $menu['name'],
        'url' => $menu['route'] ? [$menu['route']] : '#',
        'icon' => $data['icon'] ?? 'iconoir-circle', // Ikon default jika kolom data kosong
        'items' => $menu['children'] ?? [],
    ];
};

// Ambil hierarki menu sesuai ID user login
$userId = Yii::$app->user->id;
$menuItems = $userId ? MenuHelper::getAssignedMenu($userId, null, $callback) : [];
?>

<!-- leftbar-tab-menu -->
<div class="startbar d-print-none">
    <!--start brand-->
    <div class="brand">
        <a href="<?= Url::to(['/']) ?>" class="logo">
            <span>
                <img src="<?= Url::base(true) ?>/assets/images/logo-kesehatan-yustisial.jpg" alt="logo-small" class="logo-sm">
            </span>
            <span>
                <img src="<?= Url::base(true) ?>/assets/images/logo-kesehatan-yustisial-svg.svg" width="119" height="22" alt="logo-large" class="logo-lg logo-light">
                <img src="<?= Url::base(true) ?>/assets/images/logo-kesehatan-yustisial-svg.svg" width="119" height="22" alt="logo-large" class="logo-lg logo-dark">
            </span>
        </a>
    </div>
    <!--end brand-->

    <!--start startbar-menu-->
    <div class="startbar-menu">
        <div class="startbar-collapse" id="startbarCollapse" data-simplebar>
            <div class="d-flex align-items-start flex-column w-100">
                <!-- Dynamic Navigation -->
                <ul class="navbar-nav mb-auto w-100">
                    <li class="menu-label mt-2">
                        <span>Menu Navigasi</span>
                    </li>

                    <?php foreach ($menuItems as $index => $item): ?>
                        <?php 
                            $hasChildren = !empty($item['items']);
                            $collapseId = 'sidebarMenu_' . $item['id'];
                        ?>

                        <?php if ($hasChildren): ?>
                            <!-- Parent Menu (Dropdown / Collapse) -->
                            <li class="nav-item">
                                <a class="nav-link" href="#<?= $collapseId ?>" data-bs-toggle="collapse" role="button"
                                   aria-expanded="false" aria-controls="<?= $collapseId ?>">
                                    <i class="<?= Html::encode($item['icon']) ?> menu-icon"></i>
                                    <span><?= Html::encode($item['label']) ?></span>
                                </a>
                                <div class="collapse" id="<?= $collapseId ?>">
                                    <ul class="nav flex-column">
                                        <?php foreach ($item['items'] as $subItem): ?>
                                            <li class="nav-item">
                                                <a href="<?= Url::to($subItem['url']) ?>" class="nav-link">
                                                    <?= Html::encode($subItem['label']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </li>
                        <?php else: ?>
                            <!-- Single Menu (Tanpa Sub-menu) -->
                            <li class="nav-item">
                                <a class="nav-link" href="<?= Url::to($item['url']) ?>">
                                    <i class="<?= Html::encode($item['icon']) ?> menu-icon"></i>
                                    <span><?= Html::encode($item['label']) ?></span>
                                </a>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <!--end navbar-nav-->

                <!-- Box Bantuan / Call Center Bawah -->
                <div class="update-msg text-center w-100 p-2 mt-auto"> 
                    <div class="d-flex justify-content-center align-items-center thumb-md bg-danger-subtle text-danger rounded-circle mx-auto">
                        <i class="las la-phone-volume fs-20"></i>
                    </div>                   
                    <h6 class="mt-2 mb-0 fs-13">Bantuan SIMRS</h6>
                    <p class="mb-2 text-muted fs-12">IT Support Ext. 104</p>
                </div>
            </div>
        </div><!--end startbar-collapse-->
    </div><!--end startbar-menu-->    
</div><!--end startbar-->
<div class="startbar-overlay d-print-none"></div>
<!-- end leftbar-tab-menu-->