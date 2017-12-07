<?php namespace App\Controllers\UserController;

use App\Controllers\AbstractAction;
use App\Requests\UserRequests\CreateRequest;
use App\Services\UserService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Exception;
use ExtendedSlim\Http\HttpCodeConstants;
use ExtendedSlim\Http\Response;
use Slim\Http\Request;

class CreateAction extends AbstractAction
{
    /** @var UserService */
    private $userService;

    /** @var Connection */
    private $connection;

    /**
     * @param UserService $userService
     * @param Connection  $connection
     */
    public function __construct(UserService $userService, Connection $connection)
    {
        $this->userService = $userService;
        $this->connection  = $connection;
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws Exception
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $createRequest = new CreateRequest($request->getParam('name'));
        $violations    = $this->getValidator()->validate($createRequest);

        if ($violations->count() > 0)
        {
            return $response->createRestApiResponse(
                $this->createErrorResponse($violations),
                ResponseMessageConstants::VALIDATION_ERROR_ID,
                ResponseMessageConstants::VALIDATION_ERROR_MESSAGE,
                HttpCodeConstants::BAD_REQUEST
            );
        }

        return $this->executeRequest($request, $response);
    }

    /**
     * @param Request  $request
     * @param Response $response
     *
     * @return Response
     * @throws Exception
     * @throws ConnectionException
     */
    private function executeRequest(Request $request, Response $response): Response
    {
        $this->connection->beginTransaction();

        try
        {
            $this->userService->create($request->getParam('name'));

            $this->connection->commit();

            return $response->createRestApiResponse();
        }
        catch (Exception $e)
        {
            $this->connection->rollBack();

            return $response->createRestApiResponse(
                null,
                ResponseMessageConstants::UNKNOWN_ERROR_ID,
                ResponseMessageConstants::UNKNOWN_ERROR_MESSAGE,
                HttpCodeConstants::BAD_REQUEST
            );
        }
    }
}
