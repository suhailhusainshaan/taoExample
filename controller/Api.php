<?php

namespace suhailhusainshaan\taoExample\controller;
use common_exception_RestApi;
use common_Utils;
use oat\oatbox\service\ServiceManager;
use tao_actions_CommonRestModule;
use oat\generis\model\user\UserRdf;
use tao_models_classes_UserService;
use common_exception_MethodNotAllowed;
use oat\tao\model\user\TaoRoles;
use oat\generis\Helper\UserHashForEncryption;

/**
 * @OA\Get(
 *     path="taoExample/api",
 *     summary="This is a sample API",
 *     @OA\Response(
 *         response="200",
 *         description="User created",
 *         @OA\JsonContent(ref="#/components/schemas/tao.CommonRestModule.CreatedResourceResponse")
 *     ),
 *     @OA\Response(
 *         response="400",
 *         description="Invalid request data",
 *         @OA\JsonContent(ref="#/components/schemas/tao.RestTrait.FailureResponse")
 *     )
 * )
 */
class Api extends tao_actions_CommonRestModule
{
    /**
     * @OA\Schema(
     *     schema="taoExample.Api.New",
     *     type="object",
     *     allOf={
     *          @OA\Schema(ref="#/components/schemas/tao.GenerisClass.Search"),
     *          @OA\Schema(ref="#/components/schemas/tao.User.Update")
     *     }
     * )
     */

    /**
     * Optional Requirements for parameters to be sent on every service
     */

     /**
     * @param null $uri
     * @return void
     * @throws \common_exception_NotImplemented
     */
    public function get($uri = null)
    {
        
        $params = [
            'user' => UserRdf::PROPERTY_LOGIN,
            'message' => "This is a custom API in custom extension",
            'login' => "admin_suhail",
            'password' => "admin_password",
            'userLanguage' => "EN",
            'defaultLanguage' => "",
            'firstName' => "SUHAIL",
            'lastName' => "HUSAIN",
            'mail' => "",
            'roles' => "Get service"
        ];

        $this->returnSuccess([
            'success' => true,
            'data' => $params
        ], false);

        $this->returnFailure(new common_exception_RestApi('Not implemented'));
    }

    /**
     * @OA\Post(
     *     path="taoExample/api",
     *     summary="This is a sample API",
     *     @OA\Response(
     *         response="200",
     *         description="User created",
     *         @OA\JsonContent(ref="#/components/schemas/tao.CommonRestModule.CreatedResourceResponse")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Invalid request data",
     *         @OA\JsonContent(ref="#/components/schemas/tao.RestTrait.FailureResponse")
     *     )
     * )
     */

    /**
     * @param null $uri
     * @return void
     * @throws \common_exception_NotImplemented
     */
    public function getStaticData($uri = null)
    {
        /** @var tao_models_classes_UserService $userService */
        $userService = ServiceManager::getServiceManager()->get(tao_models_classes_UserService::SERVICE_ID);
        // if (!$userService->getOption(tao_models_classes_UserService::OPTION_ALLOW_API)) {
        //     $this->returnFailure(new common_exception_RestApi((new common_exception_MethodNotAllowed())->getMessage()));
        //     return;
        // }
        
        $parameters = $this->getParameters();
        $login = $parameters[UserRdf::PROPERTY_LOGIN];
        $plainPassword = $parameters[UserRdf::PROPERTY_PASSWORD];
        unset($parameters[UserRdf::PROPERTY_PASSWORD]);
        $roles = [TaoRoles::SYSTEM_ADMINISTRATOR, TaoRoles::GLOBAL_MANAGER];

        $user = $userService->addUser($login, $plainPassword, $this->getResource(array_shift($roles)));
        $userService->attachProperties($user, $parameters);
        
        foreach ($roles as $role) {
            $userService->attachRole($user, $this->getResource($role));
        }
        $userService->attachProperties($user, $parameters);
        $userService->triggerUpdatedEvent(
            $user,
            [UserRdf::PROPERTY_PASSWORD => $user->getProperty(UserRdf::PROPERTY_PASSWORD)],
            UserHashForEncryption::hash($plainPassword)
        );

        $this->returnSuccess([
                'success' => true,
                'uri' => $user->getUri(),
            ], false);

        $this->returnFailure(new common_exception_RestApi('Not implemented'));
    }

}
