<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Folder Controller
 *
 * @author dev@maarch.org
 */

namespace Folder\controllers;

use Folder\models\EntityFolderModel;
use Folder\models\FolderModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class FolderController
{
    public function get(Request $request, Response $response)
    {
        //TODO Check rights
        $folders = FolderModel::get(['order_by' => ['level']]);

        $tree = [];
        foreach ($folders as $folder) {
            $insert = [
                'name'       => $folder['label'],
                'expandable' => false,
                'id'         => $folder['id'],
                'label'      => $folder['label'],
                'public'     => $folder['public'],
                'user_id'    => $folder['user_id'],
                'parent_id'  => $folder['parent_id'],
                'level'      => $folder['level'],
            ];
            if ($folder['level'] == 0) {
                $tree[] = $insert;
            } else {
                $found = false;
                foreach ($tree as $key => $branch) {
                    if ($branch['id'] == $folder['parent_id']) {
                        array_splice($tree, $key + 1, 0, [$insert]);
                        $tree[$key]['expandable'] = true;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $tree[] = $insert;
                }
            }
        }

        return $response->withJson(['folders' => $tree]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }

        //TODO Check rights

        $folder = FolderModel::getById(['id' => $aArgs['id']]);
        if (empty($folder)) {
            return $response->withStatus(400)->withJson(['errors' => 'Folder not found']);
        }

        $folder['sharing']['entities'] = [];
        if ($folder['public']) {
            $entitiesFolder = EntityFolderModel::getByFolderId(['folder_id' => $aArgs['id']]);
            foreach ($entitiesFolder as $value) {
                $folder['sharing']['entities'][] = ['entity_id' => $value['entity_id'], 'edition' => $value['edition']];
            }
        }

        //TODO Get resources

        return $response->withJson(['folder' => $folder]);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();

        if (!Validator::stringType()->notEmpty()->validate($data['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }
        if (!empty($data['parent_id']) && !Validator::intval()->validate($data['parent_id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body parent_id is not a numeric']);
        }

        //TODO Check rights

        if (empty($data['parent_id'])) {
            $data['parent_id'] = 0;
            $level  = 0;
            $owner  = $GLOBALS['id'];
            $public = false;
        } else {
            $folder = FolderModel::getById(['id' => $data['parent_id'], 'select' => ['user_id', 'public', 'level']]);
            $owner  = $folder['user_id'];
            $public = $folder['public'];
            $level  = $folder['level'] + 1;
        }

        $id = FolderModel::create([
            'label'     => $data['label'],
            'public'    => $public,
            'user_id'   => $owner,
            'parent_id' => $data['parent_id'],
            'level'     => $level
        ]);

        if ($public) {
            $entitiesSharing = EntityFolderModel::getByFolderId(['folder_id' => $data['parent_id']]);
            foreach ($entitiesSharing as $entity) {
                EntityFolderModel::create([
                    'folder_id' => $id,
                    'entity_id' => $entity['entity_id'],
                    'edition'   => $entity['edition'],
                ]);
            }
        }

        HistoryController::add([
            'tableName' => 'folders',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _FOLDER_CREATION . " : {$data['label']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderCreation',
        ]);

        return $response->withJson(['folder' => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getParams();

        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }
        if (!Validator::stringType()->notEmpty()->validate($data['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string']);
        }
        if (!empty($data['parent_id']) &&!Validator::intval()->validate($data['parent_id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body parent_id is not a numeric']);
        }

        //TODO Check rights

        if (empty($data['parent_id'])) {
            $data['parent_id'] = 0;
            $level  = 0;
        } else {
            $folder = FolderModel::getById(['id' => $data['parent_id'], 'select' => ['level']]);
            $level  = $folder['level'] + 1;
        }

        FolderModel::update([
            'set' => [
                'label'      => $data['label'],
                'parent_id'  => $data['parent_id'],
                'level'      => $level
            ],
            'where' => ['id = ?'],
            'data' => [$aArgs['id']]
        ]);

        HistoryController::add([
            'tableName' => 'folders',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _FOLDER_MODIFICATION . " : {$data['label']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderModification',
        ]);

        return $response->withStatus(200);
    }

    public function sharing(Request $request, Response $response, array $aArgs)
    {
        $data = $request->getParams();

        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }
        if (!Validator::boolVal()->validate($data['public'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body public is empty or not a boolean']);
        }

        //TODO Check rights

        FolderModel::update([
            'set' => [
                'public' => empty($data['public']) ? 'false' : 'true',
            ],
            'where' => ['id = ?'],
            'data' => [$aArgs['id']]
        ]);

        EntityFolderModel::deleteByFolderId(['folder_id' => $aArgs['id']]);
        // TODO unshare subfolders

        if ($data['public'] && !empty($data['sharing']['entities'])) {
            //TODO check entities exists

            foreach ($data['sharing']['entities'] as $entity) {
                EntityFolderModel::create([
                    'folder_id' => $aArgs['id'],
                    'entity_id' => $entity['entity_id'],
                    'edition'   => $entity['edition'],
                ]);
            }
            // TODO share subfolders
        }

        HistoryController::add([
            'tableName' => 'folders',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _FOLDER_SHARING_MODIFICATION . " : {$data['label']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderModification',
        ]);

        return $response->withStatus(200);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!Validator::numeric()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Query id is empty or not an integer']);
        }

        //TODO Check rights

        $folder = FolderModel::getById(['id' => $aArgs['id'], 'select' => ['label']]);

        FolderModel::delete(['id' => $aArgs['id']]);
        EntityFolderModel::deleteByFolderId(['folder_id' => $aArgs['id']]);
        
        //TODO Delete sub folders
        //TODO Delete resources folders

        HistoryController::add([
            'tableName' => 'folder',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _FOLDER_SUPPRESSION . " : {$folder['label']}",
            'moduleId'  => 'folder',
            'eventId'   => 'folderSuppression',
        ]);

        return $response->withStatus(200);
    }
}
