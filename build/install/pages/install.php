<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 18.02.2017
 *
 **/
namespace build\install\pages;

use build\install\inc\iController;
use mwce\db\Connect;
use mwce\Tools\Configs;
use mwce\Tools\DicBuilder;
use mwce\Tools\html;
use mwce\Tools\Tools;

class install extends iController
{
    protected $postField = array(
        'choseBuild' => ['type'=>self::STR],
        'choseConnection' => ['type'=>self::INT],
        'db_user' => ['type'=>self::STR],
        'db_pwd' => ['type'=>self::STR],
        'adm_user' => ['type'=>self::STR],
        'adm_pwd' => ['type'=>self::STR],
        'db_address' => ['type'=>self::STR],
        'db_baseName' => ['type'=>self::STR],
    );

    private $allowableBulds;

    public function __construct(\mwce\Tools\content $view, $pages)
    {
        parent::__construct($view, $pages);
        $this->allowableBulds = Tools::getAllBuilds(false);
    }

    protected function getAllowedConnectList($buildCfg){
        $conntects = Connect::$conList;
        $avc = array();
        if(!empty($conntects) && !empty($buildCfg['availableConnections'])){
            foreach ($conntects as $id=>$val){
                if(in_array($id,$buildCfg['availableConnections']))
                    $avc[$id] = $val;
            }
        }
        return $avc;
    }

    public function actionIndex()
    {
        $bList = $this->allowableBulds;
        $bList['-1'] = '...';

        $this->view
            ->set('buildList',html::select($bList,'choseBuild','-1','class="form-control inlineBlock" onchange="chosenBuild()"'))
            ->out('main',$this->className);
    }

    /**
     * форма настройки билда для установки
     */
    public function actionGetBuildInfo(){
        if(!empty($_POST['choseBuild']) && $_POST['choseBuild']!='-1' && in_array($_POST['choseBuild'],$this->allowableBulds)){

            $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.$_POST['choseBuild'].DIRECTORY_SEPARATOR.'install.php';
            if(file_exists($path)){
                $params = require $path;
                $avc = self::getAllowedConnectList($params);

                if(!empty($params['needAdmin'])){
                    $this->view->set([
                        'aLogin' =>$params['needAdmin']['login'],
                        'aPwd' => $params['needAdmin']['pwd']
                    ]);
                }
                else
                    $this->view->set('needAdminStyle','display:none;');

                //region проверка доступа к директориям

                if(is_writable(baseDir.DIRECTORY_SEPARATOR.'configs'))
                    $dirs[0] = array('name'=>DIRECTORY_SEPARATOR.'configs','result'=>'yes','class'=>'success');
                else
                    $dirs[0] = array('name'=>DIRECTORY_SEPARATOR.'configs','result'=>'no','class'=>'danger');
                if(!empty($params['writeFolders'])){
                    foreach ($params['writeFolders'] as $folder){

                        if(is_writable(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.$_POST['choseBuild'].DIRECTORY_SEPARATOR.$folder))
                            $dirs[] = array('name'=>DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.$_POST['choseBuild'].DIRECTORY_SEPARATOR.$folder,'result'=>'yes','class'=>'success');
                        else
                            $dirs[] = array('name'=>DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.$_POST['choseBuild'].DIRECTORY_SEPARATOR.$folder,'result'=>'no','class'=>'danger');
                    }
                }

                if(!empty($dirs)){
                    foreach ($dirs as $dir){
                        $this->view
                            ->add_dict($dir)
                            ->out('dirs',$this->className);
                    }
                    $this->view->setFContainer('dirList',true);
                }

                //endregion

                if(isset($params['writeBase']) && $params['writeBase'])
                    $this->view->set('isShowBd','');
                else
                    $this->view->set('isShowBd','display:none;');

                $this->view
                    ->set('desc', !empty($params['description'])?$params['description']:$this->view->getVal('lng_aboutErr'))
                    ->set('bdList', html::select($avc,'choseConnection',0,'class="form-control inlineBlock"'))
                    ->out('buildInfo',$this->className);


                return;
            }

            $this->view
                ->set('err_title',$this->view->getVal('lng_errTitle'))
                ->set('err_desc',$this->view->getVal('lng_err1'))
                ->out('error',$this->className);
        }
    }

    public function actionSetup()
    {
        if (!empty($_POST['choseBuild'])
            && !empty($_POST['choseConnection'])
            && !empty($_POST['db_user'])
            && !empty($_POST['db_address'])
            && !empty($_POST['db_pwd'])
            && in_array($_POST['choseBuild'],$this->allowableBulds)
        ) {

            $path = baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.$_POST['choseBuild'].DIRECTORY_SEPARATOR.'install.php';
            if(file_exists($path)){
                $params = require $path;

                //region проверка на доступность подключений
                $avc = self::getAllowedConnectList($params);
                if(!in_array($_POST['choseConnection'],$avc))
                    json_encode(['error' => $this->view->getVal('lng_err4')]);
                //endregion

                //region директории на запись
                if(!is_writable(baseDir.DIRECTORY_SEPARATOR.'configs')){
                    json_encode(['error' => $this->view->getVal('lng_err3').DIRECTORY_SEPARATOR.'configs']);
                    return;
                }

                if(!empty($params['writeFolders'])){
                    foreach ($params['writeFolders'] as $folder){

                        if(!is_writable(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.$_POST['choseBuild'].DIRECTORY_SEPARATOR.$folder)){
                            json_encode(['error' => $this->view->getVal('lng_err3').DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.$_POST['choseBuild'].DIRECTORY_SEPARATOR.$folder]);
                            return;
                        }
                    }
                }
                //endregion

                if(isset($params['writeBase']) && $params['writeBase']){
                    if(empty($_POST['db_baseName'])){
                        echo json_encode(['error' => $this->view->getVal('lng_err2')]);
                        return;
                    }

                    $_SESSION['installDb'] = $_POST['db_baseName'];
                }

                try{
                    $_SESSION['installServer'] = $_POST['db_address'];
                    $_SESSION['installUsr'] = $_POST['db_user'];
                    $_SESSION['installPwd'] = $_POST['db_pwd'];
                    $_SESSION['installCt'] = $_POST['choseConnection'];
                    $db = Connect::start(-1);

                    $files = glob(baseDir.DIRECTORY_SEPARATOR.'build'.DIRECTORY_SEPARATOR.$_POST['choseBuild'].DIRECTORY_SEPARATOR.'SQL'.DIRECTORY_SEPARATOR.'*.sql');

                    if(!empty($files)){
                        if(!empty($_SESSION['installDb']))
                            $db->exec("USE ".$_SESSION['installDb']);

                        foreach ($files as $file){

                            $file = trim(file_get_contents($file));
                            if(!empty($file))
                                $db->query($file);
                        }
                    }


                    $oldCfg = Configs::globalCfg();
                    $oldCfg['defaultBuild'] = $_POST['choseBuild'];
                    $dic = new DicBuilder(baseDir.DIRECTORY_SEPARATOR.'configs'.DIRECTORY_SEPARATOR.'configs.php');
                    $dic->buildDic($oldCfg);

                    self::writeCfg($_POST['choseBuild'],[
                        'server' => $_SESSION['installServer'],
                        'db' => $params['db_name_cfg'],
                        'user' => $_SESSION['installUsr'],
                        'password' => $_SESSION['installPwd'],
                        'type' => $_SESSION['installCt'],
                    ]);

                    session_destroy();
                    $_SESSION = array();

                    echo json_encode(['success' => $this->view->getVal('lng_success')]);

                }
                catch (\Exception $e){
                    $msg = $e->getMessage();
                    if(strlen($msg) > 400)
                        $msg = substr($msg,0,400).'...';
                    echo json_encode(['error' => $msg]);
                }
            }
            return;
        }

        echo json_encode(['error' => $this->view->getVal('lng_err2')]);
    }

    /**
     * запись connections.php
     * @param string $build
     * @param $array
     * @param bool $notOne несколько массивов?
     */
    private function writeCfg($build, $array,$notOne = false)
    {
        $content = '<?php return [' . PHP_EOL;

        if(!$notOne)
        {
            $content .= '0=>[ ' . PHP_EOL;
            $content .= '"server"=>"' . $array['server'] . '", ' . PHP_EOL;
            $content .= '"db"=>"' . $array['db'] . '", ' . PHP_EOL;
            $content .= '"user"=>"' . $array['user'] . '", ' . PHP_EOL;
            $content .= '"password"=>"' . $array['password'] . '", ' . PHP_EOL;
            $content .= '"type"=>' . $array['type'] . ', ' . PHP_EOL;
            $content .= '],';
        }
        else
        {
            foreach ($array as $name=>$vals){
                if(is_numeric($name))
                    $content .= $name.'=>[ ' . PHP_EOL;
                else
                    $content .= '"'.$name.'"=>[ ' . PHP_EOL;

                $content .= '"server"=>"' . $vals['server'] . '", ' . PHP_EOL;
                $content .= '"db"=>"' . $vals['db'] . '", ' . PHP_EOL;
                $content .= '"user"=>"' . $vals['user'] . '", ' . PHP_EOL;
                $content .= '"password"=>"' . $vals['password'] . '", ' . PHP_EOL;
                $content .= '"type"=>' . $vals['type'] . ', ' . PHP_EOL;
                $content .= '],';
            }
        }


        $content .= '];';
        file_put_contents(baseDir . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $build . DIRECTORY_SEPARATOR . 'configs' . DIRECTORY_SEPARATOR . 'connections.php', $content, LOCK_EX);

    }
}