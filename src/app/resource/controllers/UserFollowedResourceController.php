<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Users Followed Resources Controller
 * @author dev@maarch.org
 */

namespace Resource\controllers;


use Attachment\models\AttachmentModel;
use Group\controllers\PrivilegeController;
use Resource\models\ResourceListModel;
use Resource\models\UserFollowedResourceModel;
use Slim\Http\Request;
use Slim\Http\Response;

class UserFollowedResourceController
{
    public function follow(Request $request, Response $response, array $args)
    {
        if (!ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])){
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $following = UserFollowedResourceModel::get([
            'where' => ['user_id = ?', 'res_id = ?'],
            'data' => [$GLOBALS['id'], $args['resId']]
        ]);

        if (!empty($following)) {
            return $response->withStatus(204);
        }

        UserFollowedResourceModel::create([
            'userId' => $GLOBALS['id'],
            'resId' => $args['resId']
        ]);

        return $response->withStatus(204);
    }

    public function unFollow(Request $request, Response $response, array $args)
    {
        if (!ResController::hasRightByResId(['resId' => [$args['resId']], 'userId' => $GLOBALS['id']])){
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $following = UserFollowedResourceModel::get([
            'where' => ['user_id = ?', 'res_id = ?'],
            'data' => [$GLOBALS['id'], $args['resId']]
        ]);

        if (empty($following)) {
            return $response->withStatus(204);
        }

        UserFollowedResourceModel::delete([
            'userId' => $GLOBALS['id'],
            'resId' => $args['resId']
        ]);

        return $response->withStatus(204);
    }

    public function getFollowedResources(Request $request, Response $response)
    {
        $followedResources = UserFollowedResourceModel::get(['select' => ['res_id'], 'where' => ['user_id = ?'], 'data' => [$GLOBALS['id']]]);
        $followedResources = array_column($followedResources, 'res_id');

        $formattedResources = [];
        $allResources = [];
        $count = 0;
        if (!empty($followedResources)) {
            $queryParams = $request->getQueryParams();
            $queryParams['offset'] = (empty($queryParams['offset']) || !is_numeric($queryParams['offset']) ? 0 : (int)$queryParams['offset']);
            $queryParams['limit'] = (empty($queryParams['limit']) || !is_numeric($queryParams['limit']) ? 10 : (int)$queryParams['limit']);

            $allQueryData = ResourceListController::getResourcesListQueryData(['data' => $queryParams]);
            if (!empty($allQueryData['order'])) {
                $data['order'] = $allQueryData['order'];
            }

            $rawResources = ResourceListModel::getOnView([
                'select'    => ['res_id'],
                'table'     => $allQueryData['table'],
                'leftJoin'  => $allQueryData['leftJoin'],
                'where'     => array_merge(['res_id in (?)'], $allQueryData['where']),
                'data'      => array_merge([$followedResources], $allQueryData['queryData']),
                'orderBy'   => empty($data['order']) ? ['creation_date'] : [$data['order']]
            ]);

            $resIds = ResourceListController::getIdsWithOffsetAndLimit(['resources' => $rawResources, 'offset' => $queryParams['offset'], 'limit' => $queryParams['limit']]);

            foreach ($rawResources as $resource) {
                $allResources[] = $resource['res_id'];
            }

            $formattedResources = [];
            if (!empty($resIds)) {
                $excludeAttachmentTypes = ['converted_pdf', 'print_folder'];
                if (!PrivilegeController::hasPrivilege(['privilegeId' => 'view_documents_with_notes', 'userId' => $GLOBALS['id']])) {
                    $excludeAttachmentTypes[] = 'document_with_notes';
                }

                $attachments = AttachmentModel::get([
                    'select'    => ['COUNT(res_id)', 'res_id_master'],
                    'where'     => ['res_id_master in (?)', 'status not in (?)', 'attachment_type not in (?)', '((status = ? AND typist = ?) OR status != ?)'],
                    'data'      => [$resIds, ['DEL', 'OBS'], $excludeAttachmentTypes, 'TMP', $GLOBALS['id'], 'TMP'],
                    'groupBy'   => ['res_id_master']
                ]);

                $select = [
                    'res_letterbox.res_id', 'res_letterbox.subject', 'res_letterbox.barcode', 'res_letterbox.alt_identifier',
                    'status.label_status AS "status.label_status"', 'status.img_filename AS "status.img_filename"', 'priorities.color AS "priorities.color"'
                ];
                $tableFunction = ['status', 'priorities'];
                $leftJoinFunction = ['res_letterbox.status = status.id', 'res_letterbox.priority = priorities.id'];

                $order = 'CASE res_letterbox.res_id ';
                foreach ($resIds as $key => $resId) {
                    $order .= "WHEN {$resId} THEN {$key} ";
                }
                $order .= 'END';

                $resources = ResourceListModel::getOnResource([
                    'select'    => $select,
                    'table'     => $tableFunction,
                    'leftJoin'  => $leftJoinFunction,
                    'where'     => ['res_letterbox.res_id in (?)'],
                    'data'      => [$resIds],
                    'orderBy'   => [$order]
                ]);

                $formattedResources = ResourceListController::getFormattedResources([
                    'resources'     => $resources,
                    'userId'        => $GLOBALS['id'],
                    'attachments'   => $attachments,
                    'checkLocked'   => false
                ]);
            }

            $count = count($rawResources);
        }

        return $response->withJson(['resources' => $formattedResources, 'countResources' => $count, 'allResources' => $allResources]);
    }
}