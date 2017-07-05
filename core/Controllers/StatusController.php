<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Status Controller
* @author dev@maarch.org
* @ingroup core
*/

namespace Core\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Respect\Validation\Validator;
use Core\Models\StatusModel;
use Core\Models\StatusImagesModel;
use Core\Models\ServiceModel;

class StatusController
{
    public function getList(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson([
            'statusList' => StatusModel::getList(),
            'lang'       => StatusModel::getStatusLang()
        ]);
    }

    public function getNewInformations(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson([
            'statusImages' => StatusImagesModel::getStatusImages(),
            'lang'         => StatusModel::getStatusLang()
        ]);
    }

    public function getByIdentifier(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!empty($aArgs['identifier']) && Validator::numeric()->validate($aArgs['identifier'])) {
            $obj = StatusModel::getByIdentifier(['identifier' => $aArgs['identifier']]);

            if (empty($obj)) {
                return $response->withStatus(404)->withJson(['errors' => 'identifier not found']);
            }

            return $response->withJson([
                'status'       => $obj,
                'lang'         => StatusModel::getStatusLang(),
                'statusImages' => StatusImagesModel::getStatusImages(),
            ]);
        } else {
            return $response->withStatus(500)->withJson(['errors' => 'identifier not valid']);
        }
    }

    public function create(RequestInterface $request, ResponseInterface $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $request = $request->getParams();
        $aArgs   = self::manageValue($request);
        $errors  = $this->control($aArgs, 'create');

        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

        if (StatusModel::create($aArgs)) {
            return $response->withJson([
                StatusModel::getById(['id' => $aArgs['id']])
            ]);
        } else {
            return $response->withStatus(500)->withJson(['errors' => _NOT_CREATE]);
        }
    }

    public function update(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $request = $request->getParams();
        $request = array_merge($request, $aArgs);

        $aArgs   = self::manageValue($request);
        $errors  = $this->control($aArgs, 'update');

        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

        if (StatusModel::update($aArgs)) {
            $return = [
                StatusModel::getByIdentifier(['identifier' => $aArgs['identifier']])
            ];

            HistoryController::add(
                'status', 
                $return[0][0]['id'], 
                'UP', 
                'statusup',
                _MODIFY_STATUS . ' : ' . $return[0][0]['id'], 
                $_SESSION['config']['databasetype']
            );

            return $response->withJson($return);
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => _NOT_UPDATE]);
        }

    }

    public function delete(RequestInterface $request, ResponseInterface $response, $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_status', 'userId' => $_SESSION['user']['UserId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $statusDeleted = StatusModel::getByIdentifier(['identifier' => $aArgs['identifier']]);

        if (Validator::notEmpty()->validate($aArgs['identifier']) && Validator::numeric()->validate($aArgs['identifier']) && !empty($statusDeleted)) {
            $return = [
                StatusModel::delete(['identifier' => $aArgs['identifier']])
            ];

            HistoryController::add(
                'status', 
                $statusDeleted[0]['id'], 
                'DEL', 
                'statusdel',
                _STATUS_DELETED . ' : ' . $statusDeleted[0]['id'], 
                $_SESSION['config']['databasetype']
            );
        } else {
            return $response
                ->withStatus(500)
                ->withJson(['errors' => 'identifier not valid']);
        }

        return $response->withJson($return);
    }

    protected function manageValue($request)
    {
        foreach ($request  as $key => $value) {
            if (in_array($key, ['is_system', 'is_folder_status', 'can_be_searched', 'can_be_modified'])) {
                if (empty($value)) {
                    $request[$key] = 'N';
                } else {
                    $request[$key] = 'Y';
                }
            }
        }

        $request['is_system'] = 'N';

        return $request;
    }

    protected function control($request, $mode)
    {
        $errors = [];

        if(!Validator::notEmpty()->validate($request['id'])){
            array_push($errors, _ID . ' ' . _EMPTY);
        } else if ($mode == 'create') {
            $obj = StatusModel::getById([
                'id' => $request['id']
            ]);
            if (!empty($obj)) {
                array_push(
                    $errors,
                    _ID . ' ' . $obj[0]['id'] . ' ' . _ALREADY_EXISTS
                );
            }
        } elseif ($mode == 'update') {
            $obj = StatusModel::getByIdentifier([
                'identifier' => $request['identifier']
            ]);
            if (empty($obj)) {
                array_push(
                    $errors,
                    $request['identifier'] . ' ' . _NOT_EXISTS
                );
            }
        }

        if (!Validator::regex('/^[\w.-]*$/')->validate($request['id']) ||
            !Validator::length(1, 10)->validate($request['id']) ||
            !Validator::notEmpty()->validate($request['id'])) {
            array_push($errors, _ID . ' ' . _INVALID);
        }

        if (!Validator::notEmpty()->validate($request['label_status']) ||
            !Validator::length(1, 50)->validate($request['label_status'])) {
            array_push($errors, _DESCRIPTION . ' ' . _INVALID);
        }

        if (Validator::notEmpty()->validate($request['is_system']) &&
            !Validator::contains('Y')->validate($request['is_system']) &&
            !Validator::contains('N')->validate($request['is_system'])
        ) {
            array_push($errors, 'is_system ' . _INVALID);
        }

        if (Validator::notEmpty()->validate($request['is_folder_status']) &&
            !Validator::contains('Y')->validate($request['is_folder_status']) &&
            !Validator::contains('N')->validate($request['is_folder_status'])
        ) {
            array_push($errors, _IS_FOLDER_STATUS . ' ' . _INVALID);
        }

        if (!Validator::notEmpty()->validate($request['img_filename']) ||
            !Validator::length(1, 255)->validate($request['img_filename'])
        ) {
            array_push($errors, _IMG_RELATED . ' ' . _INVALID);
        }

        if (Validator::notEmpty()->validate($request['maarch_module']) &&
            !Validator::length(null, 255)->validate($request['maarch_module'])
        ) {
            array_push($errors, 'maarch_module ' . _INVALID);
        }

        if (Validator::notEmpty()->validate($request['can_be_searched']) &&
            !Validator::contains('Y')->validate($request['can_be_searched']) &&
            !Validator::contains('N')->validate($request['can_be_searched'])
        ) {
            array_push($errors, _CAN_BE_SEARCHED . ' ' . _INVALID);
        }

        if (Validator::notEmpty()->validate($request['can_be_modified']) &&
            !Validator::contains('Y')->validate($request['can_be_modified']) &&
            !Validator::contains('N')->validate($request['can_be_modified'])
        ) {
            array_push($errors, _CAN_BE_MODIFIED . ' ' . _INVALID);
        }

        return $errors;
    }
}
