<?php

namespace app\modules\admin\controllers;

use Yii;
use yii\helpers\FileHelper;
use yii\web\Controller;

/**
 * Default controller for the `admin` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function scanNestedModules($currentDir, $baseNamespace = 'app')
    {
        $modules = [];

        if (!is_dir($currentDir)) {
            return $modules;
        }

        $items = scandir($currentDir);

        foreach ($items as $item) {
            if (in_array($item, ['.', '..', 'vendor', 'runtime', '.git', 'node_modules', 'assets'])) {
                continue;
            }

            $fullPath = $currentDir . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                if ($item === 'modules') {
                    $subItems = scandir($fullPath);

                    foreach ($subItems as $subItem) {
                        if ($subItem === '.' || $subItem === '..') {
                            continue;
                        }

                        $modulePath = $fullPath . DIRECTORY_SEPARATOR . $subItem;

                        if (is_dir($modulePath)) {
                            $moduleName = $subItem;
                            $moduleNamespace = $baseNamespace . '\\modules\\' . $moduleName;
                            $moduleClass = $moduleNamespace . '\\Module';

                            $moduleData = [
                                'name' => $moduleName,
                                'path' => $modulePath,
                                'class' => class_exists($moduleClass) ? $moduleClass : null,
                                'has_module_class' => file_exists($modulePath . DIRECTORY_SEPARATOR . 'Module.php'),
                                'sub_modules' => [],
                            ];

                            // Perbaikan: gunakan $this->
                            $nested = $this->scanNestedModules($modulePath, $moduleNamespace);
                            if (!empty($nested)) {
                                $moduleData['sub_modules'] = $nested;
                            }

                            $modules[$moduleName] = $moduleData;
                        }
                    }
                } else {
                    // Perbaikan: gunakan $this->
                    $deeperModules = $this->scanNestedModules($fullPath, $baseNamespace . '\\' . $item);
                    if (!empty($deeperModules)) {
                        $modules = array_merge($modules, $deeperModules);
                    }
                }
            }
        }

        return $modules;
    }

    public function extractModuleNames($modules)
    {
        $names = [];

        foreach ($modules as $module) {
            // Ambil nama modul saat ini
            if (isset($module['name'])) {
                $names[] = $module['name'];
            }

            // Jika memiliki sub_modules dan tidak kosong, telusuri ke dalamnya
            if (!empty($module['sub_modules']) && is_array($module['sub_modules'])) {
                $subNames = $this->extractModuleNames($module['sub_modules']);
                $names = array_merge($names, $subNames);
            }
        }

        return $names;
    }

    public function extractModulePaths($modules, $parentPath = '')
    {
        $paths = [];

        foreach ($modules as $module) {
            $currentPath = $parentPath ? $parentPath . '/' . $module['name'] : $module['name'];
            $paths[] = $currentPath;

            if (!empty($module['sub_modules']) && is_array($module['sub_modules'])) {
                $paths = array_merge($paths, $this->extractModulePaths($module['sub_modules'], $currentPath));
            }
        }

        return $paths;
    }
}
