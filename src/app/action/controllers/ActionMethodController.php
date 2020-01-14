<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use AcknowledgementReceipt\models\AcknowledgementReceiptModel;
use Action\models\ActionModel;
use Action\models\BasketPersistenceModel;
use Action\models\ResMarkAsReadModel;
use Attachment\models\AttachmentModel;
use Entity\controllers\ListInstanceController;
use Entity\models\ListInstanceModel;
use ExternalSignatoryBook\controllers\MaarchParapheurController;
use History\controllers\HistoryController;
use MessageExchange\controllers\MessageExchangeReviewController;
use Note\models\NoteModel;
use Resource\models\ResModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

class ActionMethodController
{
    use AcknowledgementReceiptTrait;
    use ShippingTrait;
    use ExternalSignatoryBookTrait;

    const COMPONENTS_ACTIONS = [
        'confirmAction'                         => null,
        'closeMailAction'                       => 'closeMailAction',
        'redirectAction'                        => 'redirect',
        'closeAndIndexAction'                   => 'closeAndIndexAction',
        'updateDepartureDateAction'             => 'updateDepartureDateAction',
        'enabledBasketPersistenceAction'        => 'enabledBasketPersistenceAction',
        'disabledBasketPersistenceAction'       => 'disabledBasketPersistenceAction',
        'resMarkAsReadAction'                   => 'resMarkAsReadAction',
        'sendExternalSignatoryBookAction'       => 'sendExternalSignatoryBookAction',
        'sendExternalNoteBookAction'            => 'sendExternalNoteBookAction',
        'createAcknowledgementReceiptsAction'   => 'createAcknowledgementReceipts',
        'updateAcknowledgementSendDateAction'   => 'updateAcknowledgementSendDateAction',
        'sendShippingAction'                    => 'createMailevaShippings',
        'sendSignatureBookAction'               => 'sendSignatureBook',
        'noConfirmAction'                       => null
    ];

    public static function terminateAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'resources']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['resources']);
        ValidatorModel::stringType($aArgs, ['basketName', 'note', 'history']);

        $set = ['locker_user_id' => null, 'locker_time' => null, 'modification_date' => 'CURRENT_TIMESTAMP'];

        $action = ActionModel::getById(['id' => $aArgs['id'], 'select' => ['label_action', 'id_status', 'history']]);
        if (!empty($action['id_status']) && $action['id_status'] != '_NOSTATUS_') {
            $set['status'] = $action['id_status'];
        }

        ResModel::update([
            'set'   => $set,
            'where' => ['res_id in (?)'],
            'data'  => [$aArgs['resources']]
        ]);

        foreach ($aArgs['resources'] as $resource) {
            if (!empty(trim($aArgs['note']))) {
                NoteModel::create([
                    'resId'     => $resource,
                    'user_id'   => $GLOBALS['id'],
                    'note_text' => $aArgs['note']
                ]);
            }

            if ($action['history'] == 'Y') {
                $info = "{$action['label_action']}{$aArgs['history']}";
                $info = empty($aArgs['basketName']) ? $info : "{$aArgs['basketName']} : {$info}";
                HistoryController::add([
                    'tableName' => 'res_letterbox',
                    'recordId'  => $resource,
                    'eventType' => 'ACTION#' . $aArgs['id'],
                    'moduleId'  => 'resource',
                    'eventId'   => $aArgs['id'],
                    'info'      => $info
                ]);

                MessageExchangeReviewController::sendMessageExchangeReview(['res_id' => $resource, 'action_id' => $aArgs['id'], 'userId' => $GLOBALS['userId']]);
            }
        }

        return true;
    }

    public static function closeMailAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);
        ValidatorModel::stringType($aArgs, ['note']);

        ResModel::update(['set' => ['closing_date' => 'CURRENT_TIMESTAMP'], 'where' => ['res_id = ?', 'closing_date is null'], 'data' => [$aArgs['resId']]]);

        if (CurlModel::isEnabled(['curlCallId' => 'closeResource'])) {
            $bodyData = [];
            $config = CurlModel::getConfigByCallId(['curlCallId' => 'closeResource']);

            $resource = ResModel::getById(['select' => ['external_id'], 'resId' => $aArgs['resId']]);
            $externalId = json_decode($resource['external_id'], true);

            if (!empty($externalId['localeoId'])) {
                if (!empty($config['inObject'])) {
                    foreach ($config['objects'] as $object) {
                        $select = [];
                        $tmpBodyData = [];
                        foreach ($object['rawData'] as $value) {
                            if ($value != 'note' && $value != 'localeoId') {
                                $select[] = $value;
                            }
                        }

                        if (!empty($select)) {
                            $document = ResModel::getOnView(['select' => $select, 'where' => ['res_id = ?'], 'data' => [$aArgs['resId']]]);
                        }
                        foreach ($object['rawData'] as $key => $value) {
                            if ($value == 'note') {
                                $tmpBodyData[$key] = empty($aArgs['note']) ? '' : $aArgs['note'];
                            } elseif ($value == 'localeoId') {
                                $tmpBodyData[$key] = $externalId['localeoId'];
                            } elseif (!empty($document[0][$value])) {
                                $tmpBodyData[$key] = $document[0][$value];
                            } else {
                                $tmpBodyData[$key] = '';
                            }
                        }

                        if (!empty($object['data'])) {
                            $tmpBodyData = array_merge($tmpBodyData, $object['data']);
                        }

                        $bodyData[$object['name']] = json_encode($tmpBodyData);
                    }
                }

                CurlModel::execSimple([
                    'url'           => $config['url'],
                    'headers'       => $config['header'],
                    'method'        => $config['method'],
                    'body'          => $bodyData,
                ]);
            }
        }

        return true;
    }

    public static function closeAndIndexAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        ResModel::update(['set' => ['closing_date' => 'CURRENT_TIMESTAMP'], 'where' => ['res_id = ?', 'closing_date is null'], 'data' => [$aArgs['resId']]]);

        return true;
    }

    public static function updateAcknowledgementSendDateAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'data']);
        ValidatorModel::intVal($aArgs, ['resId']);

        AcknowledgementReceiptModel::updateSendDate(['send_date' => date('Y-m-d H:i:s', $aArgs['data']['send_date']), 'res_id' => $aArgs['resId']]);

        return true;
    }

    public static function updateDepartureDateAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        ResModel::update(['set' => ['departure_date' => 'CURRENT_TIMESTAMP'], 'where' => ['res_id = ?', 'departure_date is null'], 'data' => [$aArgs['resId']]]);

        return true;
    }

    public static function disabledBasketPersistenceAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        BasketPersistenceModel::delete([
            'where' => ['res_id = ?',  'user_id = ?'],
            'data'  => [$aArgs['resId'], $GLOBALS['userId']]
        ]);

        BasketPersistenceModel::create([
            'res_id'        => $aArgs['resId'],
            'user_id'       => $GLOBALS['userId'],
            'is_persistent' => 'N'
        ]);

        return true;
    }

    public static function enabledBasketPersistenceAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId']);
        ValidatorModel::intVal($aArgs, ['resId']);

        BasketPersistenceModel::delete([
            'where' => ['res_id = ?', 'user_id = ?'],
            'data'  => [$aArgs['resId'], $GLOBALS['userId']]
        ]);

        BasketPersistenceModel::create([
            'res_id'        => $aArgs['resId'],
            'user_id'       => $GLOBALS['userId'],
            'is_persistent' => 'Y'
        ]);

        return true;
    }

    public static function resMarkAsReadAction(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['resId', 'data']);
        ValidatorModel::intVal($aArgs, ['resId']);

        ResMarkAsReadModel::delete([
            'where' => ['res_id = ?', 'user_id = ?', 'basket_id = ?'],
            'data'  => [$aArgs['resId'], $GLOBALS['userId'], $aArgs['data']['basketId']]
        ]);

        ResMarkAsReadModel::create([
            'res_id'    => $aArgs['resId'],
            'user_id'   => $GLOBALS['userId'],
            'basket_id' => $aArgs['data']['basketId']
        ]);

        return true;
    }

    public static function redirect(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId', 'data']);
        ValidatorModel::intVal($args, ['resId']);
        ValidatorModel::arrayType($args, ['data']);

        $listInstances = [];
        if (!empty($args['data']['onlyRedirectDest'])) {
            if (count($args['data']['listInstances']) == 1) {
                $listInstances = ListInstanceModel::get(['select' => ['*'], 'where' => ['res_id = ?', 'difflist_type = ?', 'item_mode != ?'], 'data' => [$args['resId'], 'entity_id', 'dest']]);
            }
        }

        $listInstances = array_merge($listInstances, $args['data']['listInstances']);
        $controller = ListInstanceController::updateListInstance(['data' => [['resId' => $args['resId'], 'listInstances' => $listInstances]], 'userId' => $GLOBALS['id']]);
        if (!empty($controller['errors'])) {
            return ['errors' => [$controller['errors']]];
        }

        return true;
    }

    public function sendSignatureBook(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $signableAttachmentsTypes = [];
        $attachmentsTypes = AttachmentModel::getAttachmentsTypesByXML();
        foreach ($attachmentsTypes as $key => $type) {
            if ($type['sign']) {
                $signableAttachmentsTypes[] = $key;
            }
        }

        $attachments = AttachmentModel::get([
            'select'    => [1],
            'where'     => ['res_id_master = ?', 'attachment_type in (?)', 'in_signature_book = ?', 'status not in (?)'],
            'data'      => [$args['resId'], $signableAttachmentsTypes, true, ['OBS', 'DEL', 'FRZ']]
        ]);
        if (empty($attachments)) {
            return ['errors' => ['No available attachments']];
        }

        return true;
    }

    public static function sendExternalNoteBookAction(array $args)
    {
        ValidatorModel::notEmpty($args, ['resId']);
        ValidatorModel::intVal($args, ['resId']);

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'modules/visa/xml/remoteSignatoryBooks.xml']);
        $config = [];

        if (!empty($loadedXml)) {
            $config['id'] = 'maarchParapheur';
            foreach ($loadedXml->signatoryBook as $value) {
                if ($value->id == $config['id']) {
                    $config['data'] = (array)$value;
                    break;
                }
            }

            $processingUserInfo = MaarchParapheurController::getUserById(['config' => $config, 'id' => $args['data']['processingUser']]);
            $sendedInfo = MaarchParapheurController::sendDatas([
                'config'            => $config,
                'resIdMaster'       => $args['resId'],
                'processingUser'    => $args['data']['processingUser'],
                'objectSent'        => 'mail',
                'userId'            => $GLOBALS['userId'],
                'note'              => $args['note'] ?? null
            ]);
            if (!empty($sendedInfo['error'])) {
                return ['errors' => [$sendedInfo['error']]];
            } else {
                $attachmentToFreeze = $sendedInfo['sended'];
            }

            $historyInfo = ' (à ' . $processingUserInfo['firstname'] . ' ' . $processingUserInfo['lastname'] . ')';
        }

        if (!empty($attachmentToFreeze)) {
            ResModel::update([
                'set' => ['external_signatory_book_id' => $attachmentToFreeze['letterbox_coll'][$args['resId']]],
                'where' => ['res_id = ?'],
                'data' => [$args['resId']]
            ]);
        }

        return ['history' => $historyInfo];
    }
}
