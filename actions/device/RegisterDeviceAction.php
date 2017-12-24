<?php
/**
 * Created by PhpStorm.
 * User: zvinger
 * Date: 24.12.17
 * Time: 15:34
 */

namespace Zvinger\Auth\Mobsolutions\actions\device;

use yii\base\Action;
use yii\web\BadRequestHttpException;
use Zvinger\Auth\Mobsolutions\components\MobileSolutionsAuthComponent;
use Zvinger\Auth\Mobsolutions\models\user\push_device\UserMobilePushDeviceObject;
use Zvinger\BaseClasses\app\exceptions\model\ModelValidateException;

class RegisterDeviceAction extends Action
{
    /**
     * @var MobileSolutionsAuthComponent
     */
    public $authComponent;

    /**
     * @throws BadRequestHttpException
     */
    public function run()
    {
        try {
            $this->save();
        } catch (ModelValidateException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }

    /**
     * @throws ModelValidateException
     */
    private function save()
    {
        $component = $this->authComponent;
        $currentTokenObject = $component->getCurrentTokenObject();
        $deviceObject = new UserMobilePushDeviceObject(
            array_merge(['token_id' => $currentTokenObject->id],
                \Yii::$app->request->post()
            )
        );
        if (!$deviceObject->save()) {
            throw new ModelValidateException($deviceObject);
        }
    }
}