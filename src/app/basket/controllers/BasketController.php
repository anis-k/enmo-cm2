<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Basket Controller
 *
 * @author dev@maarch.org
 */

namespace Basket\controllers;

use Basket\models\ActionGroupBasketModel;
use Basket\models\BasketModel;
use Action\models\ActionModel;
use Basket\models\GroupBasketModel;
use Basket\models\GroupBasketRedirectModel;
use Group\controllers\PrivilegeController;
use SrcCore\models\ValidatorModel;
use Group\models\GroupModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PreparedClauseController;
use User\models\UserBasketPreferenceModel;

class BasketController
{
    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['baskets' => BasketModel::get()]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getByBasketId(['basketId' => $aArgs['id']]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        return $response->withJson(['basket' => $basket]);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['id']) && preg_match("/^[\w-]*$/", $data['id']) && (strlen($data['id']) <= 32);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['basket_name']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['basket_desc']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['clause']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $existingBasket = BasketModel::getByBasketId(['basketId' => $data['id'], 'select' => ['1']]);
        if (!empty($existingBasket)) {
            return $response->withStatus(400)->withJson(['errors' => _ID. ' ' . _ALREADY_EXISTS]);
        }

        if (!PreparedClauseController::isRequestValid(['clause' => $data['clause'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(400)->withJson(['errors' => _INVALID_CLAUSE]);
        }

        $data['isVisible'] = empty($data['isSearchBasket']) ? 'Y' : 'N';
        $data['flagNotif'] = empty($data['flagNotif']) ? 'N' : 'Y';
        BasketModel::create($data);
        HistoryController::add([
            'tableName' => 'baskets',
            'recordId'  => $data['id'],
            'eventType' => 'ADD',
            'info'      => _BASKET_CREATION . " : {$data['id']}",
            'moduleId'  => 'basket',
            'eventId'   => 'basketCreation',
        ]);

        return $response->withJson(['basket' => $data['id']]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getByBasketId(['basketId' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['basket_name']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['basket_desc']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['clause']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!PreparedClauseController::isRequestValid(['clause' => $data['clause'], 'userId' => $GLOBALS['userId']])) {
            return $response->withStatus(400)->withJson(['errors' => _INVALID_CLAUSE]);
        }

        $data['isVisible'] = empty($data['isSearchBasket']) ? 'Y' : 'N';
        $data['flagNotif'] = empty($data['flagNotif']) ? 'N' : 'Y';
        $data['id'] = $aArgs['id'];
        BasketModel::update($data);
        HistoryController::add([
            'tableName' => 'baskets',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _BASKET_MODIFICATION . " : {$aArgs['id']}",
            'moduleId'  => 'basket',
            'eventId'   => 'basketModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getByBasketId(['basketId' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket does not exist']);
        }

        BasketModel::delete(['id' => $aArgs['id']]);
        HistoryController::add([
            'tableName' => 'baskets',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _BASKET_SUPPRESSION . " : {$aArgs['id']}",
            'moduleId'  => 'basket',
            'eventId'   => 'basketSuppression',
        ]);

        return $response->withJson(['baskets' => BasketModel::get()]);
    }

    public function getSorted(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $baskets = BasketModel::get([
            'select'    => ['basket_id', 'basket_name', 'basket_desc', 'basket_order'],
            'where'     => ['is_visible = ?'],
            'data'      => ['Y'],
            'orderBy'   => ['basket_order']
        ]);

        return $response->withJson(['baskets' => $baskets]);
    }

    public function updateSort(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        foreach ($data as $key => $basketToUpdate) {
            if ($key != $basketToUpdate['basket_order']) {
                BasketModel::updateOrder(['id' => $basketToUpdate['basket_id'], 'order' => $key]);
            }
        }

        HistoryController::add([
            'tableName' => 'baskets',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _BASKETS_SORT_MODIFICATION,
            'moduleId'  => 'basket',
            'eventId'   => 'basketModification',
        ]);

        $baskets = BasketModel::get([
            'select'    => ['basket_id', 'basket_name', 'basket_desc', 'basket_order'],
            'where'     => ['is_visible = ?'],
            'data'      => ['Y'],
            'orderBy'   => ['basket_order']
        ]);

        return $response->withJson(['baskets' => $baskets]);
    }

    public function getGroups(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getByBasketId(['basketId' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $allGroups = GroupModel::get(['select' => ['group_id', 'group_desc']]);

        $groups = GroupBasketModel::get(['where' => ['basket_id = ?'], 'data' => [$aArgs['id']], 'orderBy' => ['group_id']]);
        $allActions = ActionModel::get();

        foreach ($groups as $key => $group) {
            foreach ($allGroups as $value) {
                if ($value['group_id'] == $group['group_id']) {
                    $groups[$key]['group_desc'] = $value['group_desc'];
                }
            }
            $groups[$key]['list_display'] = json_decode($group['list_display'], true);
            $groups[$key]['list_event_data'] = json_decode($group['list_event_data'], true);
            $actionsForGroup = $allActions;
            $actions = ActionGroupBasketModel::get([
                'select'    => ['id_action', 'where_clause', 'used_in_basketlist', 'used_in_action_page', 'default_action_list'],
                'where'     => ['basket_id = ?', 'group_id = ?'],
                'data'      => [$aArgs['id'], $group['group_id']]
            ]);
            $actionIds = [];
            foreach ($actions as $action) {
                $actionIds[] = $action['id_action'];
            }
            $redirects = [];
            if (!empty($actionIds)) {
                $redirects = GroupBasketRedirectModel::get([
                    'select'    => ['entity_id', 'action_id', 'keyword', 'redirect_mode'],
                    'where'     => ['basket_id = ?', 'group_id = ?', 'action_id in (?)'],
                    'data'      => [$aArgs['id'], $group['group_id'], $actionIds]
                ]);
            }
            foreach ($actions as $actionKey => $action) {
                $actions[$actionKey]['redirects'] = [];
                foreach ($redirects as $redirect) {
                    if ($redirect['action_id'] == $action['id_action']) {
                        $actions[$actionKey]['redirects'][] = $redirect;
                    }
                }
            }

            foreach ($actionsForGroup as $actionKey => $actionForGroup) {
                foreach ($actions as $action) {
                    if ($actionForGroup['id'] == $action['id_action']) {
                        $actionsForGroup[$actionKey] = array_merge($actionForGroup, $action);
                        $actionsForGroup[$actionKey]['checked'] = true;
                        unset($actionsForGroup[$actionKey]['id_action']);
                    }
                }
                if (empty($actionsForGroup[$actionKey]['checked'])) {
                    $actionsForGroup[$actionKey]['where_clause'] = '';
                    $actionsForGroup[$actionKey]['used_in_basketlist'] = 'N';
                    $actionsForGroup[$actionKey]['used_in_action_page'] = 'Y';
                    $actionsForGroup[$actionKey]['default_action_list'] = 'N';
                    $actionsForGroup[$actionKey]['redirects'] = [];
                    $actionsForGroup[$actionKey]['checked'] = false;
                }
            }
            $groups[$key]['groupActions'] = $actionsForGroup;
        }

        return $response->withJson(['groups' => $groups, 'allGroups' => $allGroups]);
    }

    public function createGroup(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getByBasketId(['basketId' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['group_id']);
        $check = $check && Validator::arrayType()->notEmpty()->validate($data['groupActions']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }
        if (!isset($data['list_display']) || !is_array($data['list_display'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data list_display is not set or not an array']);
        }
        foreach ($data['list_display'] as $value) {
            if (!Validator::stringType()->notEmpty()->validate($value['value'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Data list_display[\'value\'] is empty or not a string']);
            } elseif (!isset($value['cssClasses']) || !is_array($value['cssClasses'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Data list_display[\'cssClasses\'] is not set or not an array']);
            }
        }

        $data['groupActions'] = BasketController::checkGroupActions(['groupActions' => $data['groupActions'], 'userId' => $GLOBALS['userId']]);
        if (!empty($data['groupActions']['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $data['groupActions']['errors']]);
        }

        if (BasketModel::hasGroup(['id' => $aArgs['id'], 'groupId' => $data['group_id']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Group already exist for this basket']);
        }

        $data['list_display'] = json_encode($data['list_display']);
        GroupBasketModel::createGroupBasket(['basketId' => $aArgs['id'], 'groupId' => $data['group_id'], 'listDisplay' => $data['list_display']]);
        foreach ($data['groupActions'] as $groupAction) {
            if ($groupAction['checked']) {
                ActionGroupBasketModel::create([
                    'id'                => $aArgs['id'],
                    'groupId'           => $data['group_id'],
                    'actionId'          => $groupAction['id'],
                    'whereClause'       => $groupAction['where_clause'],
                    'usedInBasketlist'  => $groupAction['used_in_basketlist'],
                    'usedInActionPage'  => $groupAction['used_in_action_page'],
                    'defaultActionList' => $groupAction['default_action_list']
                ]);

                if (!empty($groupAction['redirects'])) {
                    foreach ($groupAction['redirects'] as $redirect) {
                        GroupBasketRedirectModel::create([
                            'id'            => $aArgs['id'],
                            'groupId'       => $data['group_id'],
                            'actionId'      => $groupAction['id'],
                            'entityId'      => $redirect['entity_id'],
                            'keyword'       => $redirect['keyword'],
                            'redirectMode'  => $redirect['redirect_mode']
                        ]);
                    }
                }
            }
        }

        $group = GroupModel::getByGroupId(['select' => ['id'], 'groupId' => $data['group_id']]);
        $users = GroupModel::getUsersById(['select' => ['id'], 'id' => $group['id']]);
        foreach ($users as $user) {
            UserBasketPreferenceModel::create([
                'userSerialId'  => $user['id'],
                'groupSerialId' => $group['id'],
                'basketId'      => $aArgs['id'],
                'display'       => 'true',
            ]);
        }

        HistoryController::add([
            'tableName' => 'baskets',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _BASKET_GROUP_CREATION . " : {$aArgs['id']}",
            'moduleId'  => 'basket',
            'eventId'   => 'basketModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function updateGroupActions(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getByBasketId(['basketId' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }
        if (!BasketModel::hasGroup(['id' => $aArgs['id'], 'groupId' => $aArgs['groupId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Group does not exist for this basket']);
        }

        $data = $request->getParsedBody();

        if (!Validator::arrayType()->notEmpty()->validate($data['groupActions'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data groupActions is empty or not an array']);
        }
        $data['groupActions'] = BasketController::checkGroupActions(['groupActions' => $data['groupActions'], 'userId' => $GLOBALS['userId']]);
        if (!empty($data['groupActions']['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $data['groupActions']['errors']]);
        }

        GroupBasketModel::deleteGroupBasket(['basketId' => $aArgs['id'], 'groupId' => $aArgs['groupId'], 'preferences' => false, 'groupBasket' => false]);

        foreach ($data['groupActions'] as $groupAction) {
            if ($groupAction['checked']) {
                ActionGroupBasketModel::create([
                    'id'                => $aArgs['id'],
                    'groupId'           => $aArgs['groupId'],
                    'actionId'          => $groupAction['id'],
                    'whereClause'       => $groupAction['where_clause'],
                    'usedInBasketlist'  => $groupAction['used_in_basketlist'],
                    'usedInActionPage'  => $groupAction['used_in_action_page'],
                    'defaultActionList' => $groupAction['default_action_list']
                ]);

                if (!empty($groupAction['redirects'])) {
                    foreach ($groupAction['redirects'] as $redirect) {
                        GroupBasketRedirectModel::create([
                            'id'            => $aArgs['id'],
                            'groupId'       => $aArgs['groupId'],
                            'actionId'      => $groupAction['id'],
                            'entityId'      => $redirect['entity_id'],
                            'keyword'       => $redirect['keyword'],
                            'redirectMode'  => $redirect['redirect_mode']
                        ]);
                    }
                }
            }
        }

        HistoryController::add([
            'tableName' => 'baskets',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _BASKET_GROUP_MODIFICATION . " : {$aArgs['id']}",
            'moduleId'  => 'basket',
            'eventId'   => 'basketModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function updateGroup(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getByBasketId(['basketId' => $aArgs['id'], 'select' => [1]]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }
        if (!BasketModel::hasGroup(['id' => $aArgs['id'], 'groupId' => $aArgs['groupId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Group does not exist for this basket']);
        }

        $data = $request->getParsedBody();

        if (!isset($data['list_display']) || !is_array($data['list_display'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Data list_display is not set or not an array']);
        }
        foreach ($data['list_display'] as $value) {
            if (!Validator::stringType()->notEmpty()->validate($value['value'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Data list_display[\'value\'] is empty or not a string']);
            } elseif (!isset($value['cssClasses']) || !is_array($value['cssClasses'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Data list_display[\'cssClasses\'] is not set or not an array']);
            }
        }
        $data['list_display'] = json_encode($data['list_display']);
        if ($data['list_event'] == 'processDocument') {
            $listEventData = [
                'canUpdate'     => !empty($data['list_event_data']['canUpdate']),
                'defaultTab'    => $data['list_event_data']['defaultTab'] ?? 'dashboard'
            ];
        }

        GroupBasketModel::update([
            'set'   => [
                'list_display'      => $data['list_display'],
                'list_event'        => $data['list_event'],
                'list_event_data'   => empty($listEventData) ? null : json_encode($listEventData)
            ],
            'where' => ['group_id = ?', 'basket_id = ?'],
            'data'  => [$aArgs['groupId'], $aArgs['id']]
        ]);

        HistoryController::add([
            'tableName' => 'baskets',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _BASKET_GROUP_MODIFICATION . " : {$aArgs['id']}",
            'moduleId'  => 'basket',
            'eventId'   => 'basketModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function deleteGroup(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['privilegeId' => 'admin_baskets', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $basket = BasketModel::getByBasketId(['basketId' => $aArgs['id']]);
        if (empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Basket not found']);
        }

        GroupBasketModel::deleteGroupBasket(['basketId' => $aArgs['id'], 'groupId' => $aArgs['groupId'], 'preferences' => true, 'groupBasket' => true]);
        HistoryController::add([
            'tableName' => 'baskets',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _BASKET_GROUP_SUPPRESSION . " : {$aArgs['id']}",
            'moduleId'  => 'basket',
            'eventId'   => 'basketModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function getlistEventData(Request $request, Response $response, array $args)
    {
        $basket = BasketModel::getById(['id' => $args['id'], 'select' => ['basket_id']]);
        $group = GroupModel::getById(['id' => $args['groupId'], 'select' => ['group_id']]);
        if (empty($group) || empty($basket)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group or basket does not exist']);
        }

        $groupBasket = GroupBasketModel::get(['select' => ['list_event_data'], 'where' => ['basket_id = ?', 'group_id = ?'], 'data' => [$basket['basket_id'], $group['group_id']]]);

        if (empty($groupBasket[0]['list_event_data'])) {
            return $response->withJson(['listEventData' => null]);
        }

        return $response->withJson(['listEventData' => json_decode($groupBasket[0]['list_event_data'], true)]);
    }

    private static function checkGroupActions(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupActions', 'userId']);
        ValidatorModel::arrayType($aArgs, ['groupActions']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $defaultAction = false;
        $actions = ActionModel::get(['select' => ['id']]);

        foreach ($aArgs['groupActions'] as $key => $groupAction) {
            if ($groupAction['checked']) {
                $actionExists = false;
                foreach ($actions as $action) {
                    if ($action['id'] == $groupAction['id']) {
                        $actionExists = true;
                    }
                }
                if (!$actionExists) {
                    return ['errors' => 'Action does not exist'];
                }
                if ($groupAction['default_action_list'] === true) {
                    $defaultAction = true;
                }

                $aArgs['groupActions'][$key]['where_clause'] = empty($groupAction['where_clause']) ? '' : $groupAction['where_clause'];
                $aArgs['groupActions'][$key]['used_in_basketlist'] = empty($groupAction['used_in_basketlist']) ? 'N' : 'Y';
                $aArgs['groupActions'][$key]['used_in_action_page'] = empty($groupAction['used_in_action_page']) ? 'N' : 'Y';
                $aArgs['groupActions'][$key]['default_action_list'] = empty($groupAction['default_action_list']) ? 'N' : 'Y';

                if ($aArgs['groupActions'][$key]['checked'] && $aArgs['groupActions'][$key]['used_in_basketlist'] == 'N' && $aArgs['groupActions'][$key]['used_in_action_page'] == 'N') {
                    return ['errors' => 'Action must be present in action page or in action list'];
                }
                if (!empty($aArgs['groupActions'][$key]['where_clause'])) {
                    if (!PreparedClauseController::isRequestValid(['clause' => $aArgs['groupActions'][$key]['where_clause'], 'userId' => $aArgs['userId']])) {
                        return ['errors' => _INVALID_CLAUSE];
                    }
                }
            }
        }
        if (!$defaultAction) {
            return ['errors' => 'Default action needed'];
        }

        return $aArgs['groupActions'];
    }
}
