<?php namespace YMap;

include_once(MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once(MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/FS.php');
include_once(MODX_BASE_PATH . 'assets/lib/Helpers/Assets.php');

/**
 * Class YMap
 * @package YMap
 */
class YMap
{
    protected $modx;
    protected $fs;
    protected $assets;
    protected $DLTemplate;
    public $customTvName = 'Yandex Map Custom TV';
    public $tv = [];
    public $config = [
        'width'  => '100%',
        'height' => '400px',
        'zoom'   => 15
    ];
    public $tpl = '@FILE:ymap';
    public $jsListDefault = 'assets/tvs/ymap/js/scripts.json';

    /**
     * YMap constructor.
     * @param $modx
     * @param $tv
     */
    public function __construct ($modx, $tv)
    {
        $this->modx = $modx;
        $this->tv = $tv;
        $this->DLTemplate = \DLTemplate::getInstance($modx);
        $this->fs = \Helpers\FS::getInstance();
        $this->assets = \AssetsHelper::getInstance($modx);
        $this->loadConfig($tv['name']);
    }

    /**
     * @param $config
     */
    protected function loadConfig ($config)
    {
        if (empty($config)) {
            return;
        }
        $file = MODX_BASE_PATH . "assets/tvs/ymap/config/{$config}.php";
        if ($this->fs->checkFile($file)) {
            $_config = include($file);
            if (is_array($_config)) {
                $this->config = array_merge($this->config, $_config);
            }
        }
    }

    /**
     * @param $list
     * @param array $ph
     * @return string
     */
    public function renderJS ($list, $ph = [])
    {
        $js = '';
        $scripts = MODX_BASE_PATH . $list;
        if ($this->fs->checkFile($scripts)) {
            $scripts = @file_get_contents($scripts);
            $scripts = $this->DLTemplate->parseChunk('@CODE:' . $scripts, $ph);
            $scripts = json_decode($scripts, true);
            if ($scripts) {
                $scripts = $scripts['scripts'];
                foreach ($scripts as $name => $params) {
                    $script = $this->assets->registerScript($name, $params);
                    if ($script) {
                        $js .= $script;
                    }
                }
            }
        } else {
            $this->modx->logEvent(0, 3, "Cannot load {$this->jsListDefault} .", $this->customTvName);
        }

        return $js;
    }

    /**
     * @return array
     */
    public function getTplPlaceholders ()
    {
        $ph = array_merge($this->config, [
            'tv_id'    => $this->tv['id'],
            'tv_value' => empty($this->tv['value']) ? '0,0' : $this->tv['value'],
            'tv_name'  => $this->tv['name'],
        ]);

        return $ph;
    }

    /**
     * @return string
     */
    public function render ()
    {
        $ph = $this->getTplPlaceholders();
        $apiKey = $this->loadApiKey();
        $ph['js'] = $this->renderJS($this->jsListDefault, ['apiKey' => $apiKey]);
        $ph['noKey'] = empty($apiKey) ? 'true' : 'false';
        $templatePath = $this->DLTemplate->getTemplatePath();
        $templateExtension = $this->DLTemplate->getTemplateExtension();
        $this->DLTemplate->setTemplatePath('assets/tvs/ymap/tpl/');
        $this->DLTemplate->setTemplateExtension('tpl');
        $output = $this->DLTemplate->parseChunk($this->tpl, $ph);
        $this->DLTemplate->setTemplatePath($templatePath);
        $this->DLTemplate->setTemplateExtension($templateExtension);

        return $output;
    }

    /**
     * @return string
     */
    protected function loadApiKey ()
    {
        $key = '';
        $file = 'assets/tvs/ymap/config/apikey.php';
        if ($this->fs->checkFile($file)) {
            $_key = include(MODX_BASE_PATH . $file);
            if (!empty($_key) && is_scalar($_key)) {
                $key = $_key;
            }
        }

        return $key;
    }
}
