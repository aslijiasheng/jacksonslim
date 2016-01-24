<?php
class IndexService{

    public function menuData(){
        $menuData = array();
        LogPlugin::logError("get list failed!r=1","ssssss1",__CLASS__,__LINE__,__FUNCTION__);
        CurlPlugin::get('slim.demo.php', '');
        return $menuData;
    }

}
