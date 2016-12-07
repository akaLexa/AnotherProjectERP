<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 05.12.2016
 * главное горизонтальное меню
 **/
namespace build\erp\plugins;
use build\erp\plugins\m\mMainMenu;
use mwce\content;
use mwce\PluginController;


class mainMenu extends PluginController
{
    public function __construct(content $view, $plugins)
    {
        parent::__construct($view, $plugins);
        if(!empty($this->configs)){
            $m = $this->configs;
            foreach ($m as $name =>$item) {
                $this->configs[$name] = explode(',',$item);
            }
        }
    }

    public function actionIndex()
    {
        if($this->isCached('mainMenu_'.$_SESSION['mwcGroup'])) //кешик
            return;
        $list = mMainMenu::getModels();

        if(!empty($list)){
            $ai = new \ArrayIterator($list);

            $curMenu = '';
            foreach ($ai as $menu_name=>$item) {
                if($menu_name != $curMenu){
                    if(!empty($this->configs[$menu_name]) && (in_array($_SESSION['mwcGroup'],$this->configs[$menu_name])
                            || in_array(3,$this->configs[$menu_name]))){
                        $inShow =  $list[$menu_name];

                        if(count($inShow)>1){
                            $title = $inShow[0]['title'];
                            unset($inShow[0]);
                            $this->view
                                ->loops('menuContent',$inShow,'multiMenu','plugin_mainMenu')
                                ->set('title',$title)
                                ->out('multiMenu','plugin_mainMenu');
                        }
                        else{
                            $this->view
                                ->add_dict($inShow[0])
                                ->out('singlePos','plugin_mainMenu');
                        }

                        $curMenu = $menu_name;
                    }
                }
            }
        }
        if($this->cacheNeed()) //если нужен кеш
            $this->doCache('mainMenu_'.$_SESSION['mwcGroup']);
    }
}