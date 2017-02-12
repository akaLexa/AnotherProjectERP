<?php

/**
 * MuWebCloneEngine
 * Created by epmak
 * 31.12.2016
 * журнал событий
 **/
namespace build\erp\main;
use build\erp\inc\eController;
use build\erp\inc\tPaginate;
use build\erp\main\m\mEventJournal;
use mwce\Configs;
use mwce\date_;
use mwce\html_;
use mwce\router;
use mwce\Tools;


class EventJournal extends eController
{
    use tPaginate;

    protected $getField = array(
        'id' => ['type' => self::INT],
    );

    protected $postField = array(
        'onlyPushed' => ['type' => self::INT],
        'shownType' => ['type' => self::INT],
        'showPushed' => ['type' => self::INT],
        'typeEv' => ['type' => self::INT],
        'curPage' => ['type' => self::INT],
        'dFrom' => ['type' => self::DATE],
        'dTo' => ['type' => self::DATE],
    );

    public function actionIndex()
    {
        $types = mEventJournal::getType();
        $types[0]='...';
        $this->view
            ->set('dFrom', date_::intransDate('now - 5 day'))
            ->set('dTo', date_::intransDate('now + 1 day'))
            ->set('typeList',html_::select($types,'typeEv',0,'class="form-control inlineBlock" onchange="evFilter()"'))
            ->out('main',$this->className);
    }

    public function actionGetList(){
        if(!empty($_POST['dFrom']) && !empty($_POST['dTo'])){

            $shownType = empty($_POST['shownType']) ? 1 : $_POST['shownType'];
            $curPage = !empty($_POST['curPage']) ? $_POST['curPage'] : 1;

            $params = array(
                'userID' => Configs::userID(),
                'dFrom' => $_POST['dFrom'],
                'dTo' => $_POST['dTo'],
                'isTop' => !empty($_POST['showPushed']) ? 1 : 0,
            );

            if(!empty($_POST['typeEv']))
                $params['eventType'] = $_POST['typeEv'];

            switch ($shownType){
                case 1 :
                    $params['isNoticed'] = 0;
                    break;
                case 2 :
                    $params['isNoticed'] = 1;
                    break;
                case 3:
                    $params['isTop'] = 1;
                    break;
            }

            $pageCnt = mEventJournal::getCount($params);
            $pageData = Tools::paginate($pageCnt,50,$curPage);

            $params['min'] = $pageData['min'];
            $params['max'] = $pageData['max'];

            $paginatorHTML = self::paginator($curPage,$pageData['count'],5);

            $list = mEventJournal::getModels($params);

            if(!empty($list)){
                $ai = new \ArrayIterator($list);
                foreach ($ai as $item){

                    if($item['col_isTop'] == 0)
                        $item['opsStyle'] = 'opacity: 0.3;';
                    else
                        $item['opsStyle'] = 'opacity: 1;';

                    $this->view
                        ->add_dict($item)
                        ->out('center',$this->className);
                }

                if($pageData['count'] >1){
                    $this->view
                        ->set('paginator',$paginatorHTML)
                        ->out('paginator',$this->className);
                }
            }
            else
                $this->view->out('emptyCenter',$this->className);
        }
        else
            $this->view->out('emptyCenter',$this->className);
    }

    /**
     * открепить/закрепить евент
     */
    public function actionPushEvent(){
        if(!empty($_GET['id'])){
            $obj = mEventJournal::getCurModel($_GET['id']);
            if(empty($obj)){
                echo json_encode(['status'=>0]);
            }
            else{
                echo json_encode(['status'=>$obj->pushEvent()]);
            }
        }
    }

    /**
     * отметить прочитанным
     */
    public function actionReadEvent(){
        if(!empty($_GET['id'])){
            $obj = mEventJournal::getCurModel($_GET['id']);
            $obj->setIsRead();
        }
    }

    /**
     * отметить ВСЕ рочитанными
     */
    public function actionSetAllRead(){
        mEventJournal::setAllRead(Configs::userID());
    }

}