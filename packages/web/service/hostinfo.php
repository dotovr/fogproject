<?php
require('../commons/base.inc.php');
header('Content-Type: text/plain');
header('Connection: close');
$Host = $FOGCore->getHostItem(false);
$Task = $Host->get('task');
$TaskType = FOGCore::getClass('TaskType',$Task->get('typeID'));
$Image = $Task->getImage();
if ($TaskType->isInitNeededTasking()) {
    if ($TaskType->isMulticast()) {
        $MulticastSession = FOGCore::getClass('MulticastSessions',@max(FOGCore::getSubObjectIDs('MulticastSessionsAssociation',array('taskID'=>$Task->get('id')))));
        if ($MulticastSession->isValid() && $Task->getImage()->get('id') != $MulticastSession->get('image')) {
            $Task->set('imageID',$MulticastSession->get('imageID'))->save();
            $Host->set('imageID',$MulticastSession->get('imageID'));
            $Image = $Host->getImage();
        }
        $port = $MulticastSession->get('port');
    }
    $StorageGroup = $Image->getStorageGroup();
    $StorageNode = $StorageGroup->getOptimalStorageNode($Image->get('id'));
    $HookManager->processEvent('BOOT_TASK_NEW_SETTINGS',array('Host' => &$Host,'StorageNode' => &$StorageNode,'StorageGroup' => &$StorageGroup));
    $osid = $Image->get('osID');
    $storage = sprintf('%s:/%s/%s',trim($StorageNode->get('ip')),trim($StorageNode->get('path'),'/'),($TaskType->isUpload() ? 'dev/' : ''));
    $ftp = $StorageNode->isValid() ? $StorageNode->get('ip') : self::getSetting('FOG_TFTP_HOST');
    $storageip = $FOGCore->resolveHostname($StorageNode->get('ip'));
    $img = $Image->get('path');
    $imgFormat = $Image->get('format');
    $imgType = $Image->getImageType()->get('type');
    $imgPartitionType = $Image->getImagePartitionType()->get('type');
    $imgid = $Image->get('id');
    $PIGZ_COMP = $Image->get('compress');
    $shutdown = intval((bool)$Task->get('shutdown'));
    $hostearly = intval((bool)FOGCore::getSetting('FOG_CHANGE_HOSTNAME_EARLY'));
    $pct = FOGCore::getSetting('FOG_UPLOADRESIZEPCT');
    if (!($pct < 100 && $pct > 4)) $pct = 5;
    $ignorepg = intval((bool)FOGCore::getSetting('FOG_UPLOADIGNOREPAGEHIBER'));
    list($mining,$miningcores,$miningpath) = FOGCore::getSubObjectIDs('Service',array('name' => array('FOG_MINING_ENABLE','FOG_MINING_MAX_CORES','FOG_MINING_PACKAGE_PATH')),'value',false,'AND','name',false,'');
    if ($TaskType->get('id') === 11) $winuser = $Task->get('passreset');
}
$fdrive = $Host->get('kernelDevice');
$Inventory = $Host->get('inventory');
$mac = $_REQUEST['mac'];
$MACs = $Host->getMyMacs();
$clientMacs = array_filter((array)$FOGCore->parseMacList(implode('|',(array)$MACs),false,true));
$repFields = array(
    // Imaging items to set
    'mac' => $mac,
    'ftp' => $ftp,
    'osid' => $osid,
    'storage' => $storage,
    'storageip' => $storageip,
    'img' => $img,
    'imgFormat' => $imgFormat,
    'imgType' => $imgType,
    'imgPartitionType' => $imgPartitionType,
    'imgid' => $imgid,
    'PIGZ_COMP' => sprintf('-%s',$PIGZ_COMP),
    'shutdown' => $shutdown,
    'hostearly' => $hostearly,
    'pct' => $pct,
    'ignorepg' => $ignorepg,
    'winuser' => $winuser,
    // Really only needed for multicast
    'port' => $port,
    // Implicit device to use
    'fdrive' => $fdrive,
    // Mining coins donation method,
    'mining' => $mining,
    'miningcores' => $miningcores,
    'miningpath' => $miningpath,
    // Exposed other elements
    'hostname' => $Host->get('name'),
    'hostdesc' => $Host->get('description'),
    'hostip' => $Host->get('ip'),
    'hostimageid' => $Host->get('imageID'),
    'hostbuilding' => $Host->get('building'),
    'hostusead' => $Host->get('useAD'),
    'hostaddomain' => $Host->get('ADDomain'),
    'hostaduser' => $Host->get('ADUser'),
    'hostadou' => $Host->get('ADOU'),
    'hostproductkey' => $Host->get('productKey'),
    'imagename' => $Image->get('name'),
    'imagedesc' => $Image->get('description'),
    'imageosid' => $osid,
    'imagepath' => $img,
    'primaryuser' => $Inventory->get('primaryuser'),
    'othertag' => $Inventory->get('other1'),
    'othertag1' => $Inventory->get('other2'),
    'sysman' => $Inventory->get('sysman'),
    'sysproduct' => $Inventory->get('sysproduct'),
    'sysserial' => $Inventory->get('sysserial'),
    'mbman' => $Inventory->get('mbman'),
    'mbserial' => $Inventory->get('mbserial'),
    'mbasset' => $Inventory->get('mbasset'),
    'mbproductname' => $Inventory->get('mbproductname'),
    'caseman' => $Inventory->get('caseman'),
    'caseserial' => $Inventory->get('caseserial'),
    'caseasset' => $Inventory->get('caseasset'),
);
$HookManager->processEvent('HOST_INFO_EXPOSE',array('repFields'=>&$repFields,'Host'=>&$Host));
array_walk($repFields,function(&$val,$key) {
    printf("export %s=%s\n",$key,escapeshellarg($val));
    unset($val,$key);
});
