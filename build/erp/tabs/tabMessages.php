<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 27.12.2016
 * вкладка переписки
 **/
namespace build\erp\tabs;
use build\erp\inc\AprojectTabs;
use build\erp\inc\User;
use build\erp\tabs\m\mTabMessages;
use mwce\Configs;
use mwce\router;
use mwce\Tools;


class tabMessages extends AprojectTabs
{
    protected $postField = array(
        'messageText' => ['type'=>self::STR],
    );
    /**
     * главный вид по умолчанию
     * @param null $params
     * @return void
     */
    public function In($params = null)
    {
        $settings = self::getProperties();
        $role = explode(',',$settings['userAccessRW']);
        $group = explode(',',$settings['groupAccessRW']);

        if(in_array(Configs::curGroup(),$group) || in_array(3,$group)
            || in_array(Configs::curRole(),$role)){
            $this->view->emptyName('isDisabled');
        }
        else
            $this->view->set('isDisabled',' DISABLED ');

        $userlist = User::getUserList();
        asort($userlist);
        unset($userlist[Configs::userID()]);

        //todo: подумать как лучше запилить форму пользователей, особенно, если их будет очень многою справочники?
        if(!empty($userlist)){
            $list = array();
            foreach ($userlist as $id=>$name){
                if(file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $id .'.png')){
                    $di = $id;
                }
                else{
                    $di = 'default';
                }
                $list[] = array('fio'=>$name,'fioID'=>$id,'curuserImg'=>$di);
            }
        }
        else
            $list = array(0=>['fio'=>'Никого нет','fioID'=>0,'curuserImg'=>'stop']);

        self::getList();

        $this->view
            ->setFContainer('messageTabContent',true)
            ->loops('listenersList',$list,'main',$this->className)
            ->out('main',$this->className);
    }

    public function getList(){
        if(!empty($this->project['col_projectID'])){
            $list = mTabMessages::getModels(['projectID'=>$this->project['col_projectID']]);
            if(!empty($list)){
                foreach ($list as $item) {
                    if(file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $item['col_AuthorID'] .'.png')){
                        $di = $item['col_AuthorID'];
                    }
                    else{
                        $di = 'default';
                    }
                    $this->view
                        ->add_dict($item)
                        ->set('curuserImg',$di)
                        ->out('message',$this->className);
                }
            }
            else{
                $this->view->out('emptyMessage',$this->className);
            }
        }
    }

    public function addComment(){
        if(!empty($_GET['id']) && !empty($_POST['messageText'])){

            $ai = new \ArrayIterator($_POST);
            $listeners = array();
            foreach ($ai as $pId=>$pVal){
                if(stripos($pId,'fio_')!== false && !empty($pVal)){
                    $listeners[] = (int)$pVal;
                }
            }

            mTabMessages::addComment($_GET['id'],$_POST['messageText'],Configs::userID(),$listeners);
            echo json_encode(['success'=>1]);
        }
    }



}