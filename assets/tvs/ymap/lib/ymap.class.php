<?php namespace YMap;

include_once (MODX_BASE_PATH . 'assets/snippets/DocLister/lib/DLTemplate.class.php');
include_once (MODX_BASE_PATH . 'assets/lib/APIHelpers.class.php');
require_once (MODX_BASE_PATH . 'assets/lib/Helpers/FS.php');
require_once (MODX_BASE_PATH . 'assets/lib/Helpers/Assets.php');
class YMap {
    public $modx = null;
    protected $fs = null;
    protected $assets = null;
    protected $DLTemplate = null;
    public $customTvName = 'Yandex Map Custom TV';
    public $tv = array();
    public $tpl = 'assets/tvs/ymap/tpl/ymap.tpl';
    public $jsListDefault = 'assets/tvs/ymap/js/scripts.json';
    
    public function __construct($modx, $tv) {
        $this->modx = $modx;
        $this->tv = $tv;
        $this->DLTemplate = \DLTemplate::getInstance($modx);
        $this->fs = \Helpers\FS::getInstance(); 
        $this->assets = \AssetsHelper::getInstance($modx);
    }

      public function prerender() {
        $output = '';
        $plugins = $this->modx->pluginEvent;
        if((array_search('ManagerManager', $plugins['OnDocFormRender']) === false) && !isset($this->modx->loadedjscripts['jQuery'])) {
            $output .= $this->assets->registerScript('jQuery',array(
                'src'=>'assets/js/jquery/jquery-1.9.1.min.js',
                'version'=>'1.9.1'
            ));
            $output .='<script type="text/javascript">var jQuery = jQuery.noConflict(true);</script>';
        }
        $tpl = MODX_BASE_PATH.$this->tpl;
        if($this->fs->checkFile($tpl)) {
            $output .= '[+js+]'.file_get_contents($tpl);
        } else {
            $this->modx->logEvent(0, 3, "Cannot load {$this->tpl} .", $this->customTvName);
            return false;
        }
        return $output;
    }

    /**
     * @param $list
     * @param array $ph
     * @return string
     */
    public function renderJS($list,$ph = array()) {
        $js = '';
        $scripts = MODX_BASE_PATH.$list;
        if($this->fs->checkFile($scripts)) {
            $scripts = @file_get_contents($scripts);
            $scripts = $this->DLTemplate->parseChunk('@CODE:'.$scripts,$ph);
            $scripts = json_decode($scripts,true);
            $scripts = $scripts['scripts'];
            foreach ($scripts as $name => $params) {
                $script = $this->assets->registerScript($name,$params);
                if ($script) $js .= $script;
            }
        } else {
           $this->modx->logEvent(0, 3, "Cannot load {$this->jsListDefault} .", $this->customTvName);
        }
        return $js;
    }

    public function getTplPlaceholders() {
        $ph = array (
            'tv_id'      => $this->tv['id'],
            'tv_value'   => empty($this->tv['value']) ? '0,0' : $this->tv['value'],
            'tv_name'    => $this->tv['name']
        );
        return $ph;
    }

    /**
     * @return string
     */
    public function render() {
        $output = $this->prerender();
        if ($output !== false) {
           $ph = $this->getTplPlaceholders();
           $ph['js'] = $this->renderJS($this->jsListDefault,$ph);
           $output = $this->DLTemplate->parseChunk('@CODE:'.$output,$ph);
        }
        return $output;
    }
}