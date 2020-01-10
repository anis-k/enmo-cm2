<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief List Instance Controller
 * @author dev@maarch.org
 */

namespace Entity\controllers;

use Entity\models\ListInstanceHistoryDetailModel;
use Entity\models\ListInstanceHistoryModel;
use Entity\models\ListInstanceModel;
use Group\controllers\PrivilegeController;
use Slim\Http\Request;
use Slim\Http\Response;
use Respect\Validation\Validator;
use Resource\controllers\ResController;
use Entity\models\EntityModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserEntityModel;
use User\models\UserModel;
use Resource\models\ResModel;

class ListInstanceController
{
    public function getById(Request $request, Response $response, array $aArgs)
    {
        $listinstance = ListInstanceModel::getById(['id' => $aArgs['id']]);

        return $response->withJson($listinstance);
    }

    public function getByResId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->validate($args['resId']) || !ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $listInstances = ListInstanceModel::get(['select' => ['*'], 'where' => ['res_id = ?', 'difflist_type = ?'], 'data' => [$args['resId'], 'entity_id']]);
        foreach ($listInstances as $key => $value) {
            if ($value['item_type'] == 'entity_id') {
                $listInstances[$key]['labelToDisplay'] = Entitymodel::getByEntityId(['entityId' => $value['item_id'], 'select' => ['entity_label']])['entity_label'];
                $listInstances[$key]['descriptionToDisplay'] = '';
            } else {
                $listInstances[$key]['labelToDisplay'] = UserModel::getLabelledUserById(['login' => $value['item_id']]);
                $listInstances[$key]['descriptionToDisplay'] = UserModel::getPrimaryEntityByUserId(['userId' => $value['item_id']])['entity_label'];
            }
        }

        return $response->withJson(['listInstance' => $listInstances]);
    }

    public function getVisaCircuitByResId(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $listInstances = ListInstanceModel::getVisaCircuitByResId(['select' => ['listinstance_id', 'sequence', 'item_id', 'item_type', 'users.id', 'firstname as item_firstname', 'lastname as item_lastname', 'entity_label as item_entity', 'viewed', 'process_date', 'process_comment', 'signatory', 'requested_signature'], 'id' => $aArgs['resId']]);
        foreach ($listInstances as $key => $value) {
            $listInstances[$key]['item_id'] = $listInstances[$key]['id'];
            $listInstances[$key]['labelToDisplay'] = $listInstances[$key]['item_firstname'].' '.$listInstances[$key]['item_lastname'];
        }

        return $response->withJson($listInstances);
    }

    public function getOpinionCircuitByResId(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['resId']) || !ResController::hasRightByResId(['resId' => [$aArgs['resId']], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $listInstances = ListInstanceModel::getAvisCircuitByResId(['select' => ['listinstance_id', 'sequence', 'item_id', 'item_type', 'users.id', 'firstname as item_firstname', 'lastname as item_lastname', 'entity_label as item_entity', 'viewed', 'process_date', 'process_comment'], 'id' => $aArgs['resId']]);
        foreach ($listInstances as $key => $value) {
            $listInstances[$key]['item_id'] = $listInstances[$key]['id'];
            $listInstances[$key]['labelToDisplay'] = $listInstances[$key]['item_firstname'].' '.$listInstances[$key]['item_lastname'];
        }

        return $response->withJson($listInstances);
    }

    public function update(Request $request, Response $response)
    {
        $body = $request->getParsedBody();
        if (!Validator::arrayType()->notEmpty()->validate($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or not an array']);
        }

        $currentUser = UserModel::getByLogin(['login' => $GLOBALS['userId'], 'select' => ['id']]);

        $controller = ListInstanceController::updateListInstance(['data' => $body, 'userId' => $currentUser['id']]);
        if (!empty($controller['errors'])) {
            return $response->withStatus($controller['code'])->withJson(['errors' => $controller['errors']]);
        }

        return $response->withStatus(204);
    }

    public static function updateListInstance(array $args)
    {
        ValidatorModel::notEmpty($args, ['data', 'userId']);
        ValidatorModel::arrayType($args, ['data']);
        ValidatorModel::intVal($args, ['userId']);

        $currentUser = UserModel::getById(['select' => ['user_id'], 'id' => $args['userId']]);

        DatabaseModel::beginTransaction();

        foreach ($args['data'] as $ListInstanceByRes) {
            if (empty($ListInstanceByRes['resId'])) {
                DatabaseModel::rollbackTransaction();
                return ['errors' => 'resId is empty', 'code' => 400];
            }

            if (!Validator::intVal()->validate($ListInstanceByRes['resId']) || !ResController::hasRightByResId(['resId' => [$ListInstanceByRes['resId']], 'userId' => $GLOBALS['id']])) {
                DatabaseModel::rollbackTransaction();
                return ['errors' => 'Document out of perimeter', 'code' => 403];
            }

            if (empty($ListInstanceByRes['listInstances'])) {
                continue;
            }

            $listInstances = ListInstanceModel::get([
                'select'    => ['*'],
                'where'     => ['res_id = ?', 'difflist_type = ?'],
                'data'      => [$ListInstanceByRes['resId'], $ListInstanceByRes['listInstances'][0]['difflist_type']]
            ]);
            ListInstanceModel::delete([
                'where' => ['res_id = ?', 'difflist_type = ?'],
                'data'  => [$ListInstanceByRes['resId'], $ListInstanceByRes['listInstances'][0]['difflist_type']]
            ]);

            if ($ListInstanceByRes['listInstances'][0]['difflist_type'] == 'entity_id') {
                $recipientFound = false;
                foreach ($ListInstanceByRes['listInstances'] as $instance) {
                    if ($instance['item_mode'] == 'dest') {
                        $recipientFound = true;
                    }
                }
                if (!$recipientFound) {
                    return ['errors' => 'Dest is missing', 'code' => 403];
                }
            }

            foreach ($ListInstanceByRes['listInstances'] as $instance) {
                $listControl = ['item_id', 'item_type', 'item_mode', 'difflist_type'];
                foreach ($listControl as $itemControl) {
                    if ($itemControl == 'item_mode' && $ListInstanceByRes['listInstances'][0]['difflist_type'] != 'entity_id') {
                        continue;
                    }
                    if (empty($instance[$itemControl])) {
                        return ['errors' => "ListInstance {$itemControl} is not set or empty", 'code' => 400];
                    }
                }

                if (in_array($instance['item_type'], ['user_id', 'user'])) {
                    if ($instance['item_type'] == 'user_id') {
                        $user = UserModel::getByLogin(['login' => $instance['item_id'], 'select' => ['id']]);
                    } else {
                        $user = UserModel::getById(['id' => $instance['item_id'], 'select' => ['id', 'user_id']]);
                        $instance['item_id'] = $user['user_id'] ?? null;
                        $instance['item_type'] = 'user_id';
                    }
                    if (empty($user)) {
                        DatabaseModel::rollbackTransaction();
                        return ['errors' => 'User not found', 'code' => 400];
                    }
                    if ($ListInstanceByRes['listInstances'][0]['difflist_type'] == 'VISA_CIRCUIT') {
                        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'visa_documents', 'userId' => $user['id']]) && !PrivilegeController::hasPrivilege(['privilegeId' => 'sign_document', 'userId' => $user['id']])) {
                            DatabaseModel::rollbackTransaction();
                            return ['errors' => 'User has not enough privileges', 'code' => 400];
                        }
                        $instance['item_mode'] = $instance['requested_signature'] ? 'sign' : 'visa';
                    } elseif ($ListInstanceByRes['listInstances'][0]['difflist_type'] == 'AVIS_CIRCUIT') {
                        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'avis_documents', 'userId' => $user['id']])) {
                            DatabaseModel::rollbackTransaction();
                            return ['errors' => 'User has not enough privileges', 'code' => 400];
                        }
                        $instance['item_mode'] = 'avis';
                    }
                } elseif (in_array($instance['item_type'], ['entity_id', 'entity'])) {
                    if ($instance['item_type'] == 'entity_id') {
                        $entity = EntityModel::getByEntityId(['entityId' => $instance['item_id'], 'select' => ['enabled']]);
                    } else {
                        $entity = EntityModel::getById(['id' => $instance['item_id'], 'select' => ['enabled', 'entity_id']]);
                        $instance['item_id'] = $entity['entity_id'] ?? null;
                        $instance['item_type'] = 'entity_id';
                    }

                    if (empty($entity) || $entity['enabled'] != 'Y') {
                        DatabaseModel::rollbackTransaction();
                        return ['errors' => 'Entity not found or not active', 'code' => 400];
                    }
                } else {
                    DatabaseModel::rollbackTransaction();
                    return ['errors' => 'item_type does not exist', 'code' => 400];
                }

                ListInstanceModel::create([
                    'res_id'            => $ListInstanceByRes['resId'],
                    'sequence'          => 0,
                    'item_id'           => $instance['item_id'],
                    'item_type'         => $instance['item_type'],
                    'item_mode'         => $instance['item_mode'],
                    'added_by_user'     => $currentUser['user_id'],
                    'difflist_type'     => $instance['difflist_type'],
                    'process_date'      => $instance['process_date'] ?? null,
                    'process_comment'   => $instance['process_comment'] ?? null,
                    'viewed'            => empty($instance['viewed']) ? 0 : $instance['viewed']
                ]);

                if ($instance['item_mode'] == 'dest') {
                    $set = ['dest_user' => $instance['item_id']];
                    $changeDestination = true;
                    $entities = UserEntityModel::get(['select' => ['entity_id', 'primary_entity'], 'where' => ['user_id = ?'], 'data' => [$instance['item_id']]]);
                    $resource = ResModel::getById(['select' => ['destination'], 'resId' => $ListInstanceByRes['resId']]);
                    foreach ($entities as $entity) {
                        if ($entity['entity_id'] == $resource['destination']) {
                            $changeDestination = false;
                        }
                        if ($entity['primary_entity'] == 'Y') {
                            $destPrimaryEntity = $entity['entity_id'];
                        }
                    }
                    if ($changeDestination && !empty($destPrimaryEntity)) {
                        $set['destination'] = $destPrimaryEntity;
                    }

                    ResModel::update([
                        'set'   => $set,
                        'where' => ['res_id = ?'],
                        'data'  => [$ListInstanceByRes['resId']]
                    ]);
                }
            }

            $listInstanceHistoryId = ListInstanceHistoryModel::create(['resId' => $ListInstanceByRes['resId'], 'userId' => $args['userId']]);
            foreach ($listInstances as $listInstance) {
                ListInstanceHistoryDetailModel::create([
                    'listinstance_history_id'   => $listInstanceHistoryId,
                    'res_id'                    => $listInstance['res_id'],
                    'sequence'                  => $listInstance['sequence'],
                    'item_id'                   => $listInstance['item_id'],
                    'item_type'                 => $listInstance['item_type'],
                    'item_mode'                 => $listInstance['item_mode'],
                    'added_by_user'             => $listInstance['added_by_user'],
                    'difflist_type'             => $listInstance['difflist_type'],
                    'process_date'              => $listInstance['process_date'] ?? null,
                    'process_comment'           => $listInstance['process_comment'] ?? null
                ]);
            }
        }

        DatabaseModel::commitTransaction();

        return ['success' => 'success'];
    }
}
