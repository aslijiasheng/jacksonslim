<?php
class IndexController extends CController{

    public function loginRed(){
        $indexService = new IndexService();
        $indexService->menuData();
        $this->redirect('task'); 
    }

    public function menuData(){
        $menuData = array();
        LogPlugin::logError("get list failed!r=1","ssssss1",__CLASS__,__LINE__,__FUNCTION__);
        CurlPlugin::get('slim.demo.php', '');
        return $menuData;
    }
}
