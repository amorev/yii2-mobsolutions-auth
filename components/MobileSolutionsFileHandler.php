<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 28.12.17
 * Time: 22:09
 */

namespace Zvinger\Auth\Mobsolutions\components;

use Zvinger\Auth\Mobsolutions\models\photo\MobileSolutionsFileInfo;
use Zvinger\Auth\Mobsolutions\models\photo\MobileSolutionsPhotoInfo;
use Zvinger\BaseClasses\app\modules\fileStorage\VendorFileStorageModule;

class MobileSolutionsFileHandler
{
    private $_file_id;

    private $_fileStorageModule;

    /**
     * MobileSolutionsFileHandler constructor.
     * @param $_file_id
     */
    public function __construct($_file_id, VendorFileStorageModule $fileStorageModule)
    {
        $this->_file_id = $_file_id;
        $this->_fileStorageModule = $fileStorageModule;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function getPhotoInfo()
    {
        $result = new MobileSolutionsPhotoInfo();
        $object = $this->_fileStorageModule->storage->getFile($this->_file_id);
        $result->PhotoId = $this->_file_id;
        if (empty($object)) {
            return NULL;
        }
        $result->photo75 =
        $result->photo130 =
        $result->photo640 =
        $result->photo860 =
        $result->photo1280 =
        $result->photo2560 = $object->getFullUrl();

        return $result;
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function getFileInfo()
    {
        $result = new MobileSolutionsFileInfo();
        $object = $this->_fileStorageModule->storage->getFile($this->_file_id);
        if (!empty($object)) {
            $result->fileId = $this->_file_id;
            $result->fileName = $object->fileStorageElement->path;
            $result->fileUrl = $object->getFullUrl();
        }

        return $result;
    }
}