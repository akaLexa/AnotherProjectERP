<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 20.04.2017
 * карточка пользователя
 **/
namespace build\erp\user;
use build\erp\inc\eController;
use build\erp\inc\Task;
use build\erp\user\m\mUserCart;
use mwce\Tools\Configs;
use mwce\Tools\Date;
use mwce\Tools\Tools;

class UserCart extends eController
{
    protected $getField = array(
        'user' => ['type'=>self::INT],
    );

    public function actionIndex()
    {
        $user = !empty($_GET['user']) ? $_GET['user'] : Configs::userID();
        $obj = mUserCart::getCurModel($user);

        if(!empty($obj)) {

            if($obj['col_isBaned'] == 0)
                $this->view->set('vizStyle','display:none');

            if(empty($obj['col_deputyID']))
                $this->view->set('vizDepStyle','display:none');

            //region список задач
            $taskList = Task::getModels([
                'taskResp'=>$user,
                'taskStatus' => 1
            ]);

            if(!empty($taskList)){
                foreach ($taskList as $item){
                    $this->view
                        ->add_dict($item)
                        ->out('taskList',$this->className);
                }

            }
            else
                $this->view->out('emptyTask',$this->className);

            $this->view->setFContainer('TLContent',true);
            //endregion

            //region список стадий
            $stages = $obj->getProjectStageList($user);
            if(!empty($stages)){
                foreach ($stages as $item){
                    $item['col_dateEndPlan'] = Date::transDate($item['col_dateEndPlan']);
                    $this->view
                        ->add_dict($item)
                        ->out('stageList',$this->className);
                }
            }
            else
                $this->view->out('emptyStage',$this->className);
            $this->view->setFContainer('SLContent',true);
            //endregion

            $this->view
                ->set('imgNum',
                    (file_exists(baseDir . DIRECTORY_SEPARATOR . 'theme' . DIRECTORY_SEPARATOR . 'imgs' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR . $user . '.png') ? $user : 'default'))
                ->add_dict($obj)
                ->add_dict($obj->getTaskStatistic($user))
                ->add_dict($obj->getStageStatistic($user))
                ->out('main', $this->className);


        }
        else{
            $this->view->out('empty',$this->className);
        }
    }
}