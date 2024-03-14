<?php

namespace TaliumAbstract\Trait;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;

trait ApiResponse
{
    protected function respondNotFound(string $message = 'Resource not found'): JsonResponse
    {
        return $this->respondError($message, 404);
    }

    protected function responseException(\Exception $e)
    {
        if ($e instanceof ValidationException) {
            return response([
                'response' => [
                    // 'message' => 'validation error!',
                    'errors' => $e->validator->getMessageBag()
                ]
            ]);
        }
        return $this->respondError($e->getMessage());
    }

    protected function respondWithSuccess($response = null, string $message = 'Success'): JsonResponse
    {
        return $this->respond(true, $response, $message, 200);
    }

    protected function respondOk(string $message = 'OK'): JsonResponse
    {
        return response()->json(['message' => $message, 'success' => true], 200);
    }

    protected function respondUnauthenticated(string $message = 'Unauthenticated'): JsonResponse
    {
        return $this->respondError($message, 401, false);
    }

    protected function respondForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->respondError($message, 403, false);
    }

    protected function respondError(string $message = 'Internal Server Error', int $statusCode = 500, bool $success = false): JsonResponse
    {
        return response()->json(['error' => ['message' => $message], 'success' => $success], $statusCode);
    }

    protected function respondCreated($response = null, string $message = 'Created Successfully'): JsonResponse
    {
        return $this->respond(true, $response, $message, 201);
    }

    protected function respondValidationError($validationErrors, string $message = 'Validation Errors'): JsonResponse
    {
        return $this->respondError($message, 422, false, $validationErrors);
    }

    protected function respondNoContent(string $message = 'No Content'): JsonResponse
    {
        return response()->json(['message' => $message, 'success' => true], 204);
    }

    protected function respond(bool $success, $response = null, string $message = '', int $statusCode = 200): JsonResponse
    {
        $result = ['message' => $message, 'success' => $success];

        if (!is_null($response)) {
            $result['response'] = $response;
        }

        return response()->json($result, $statusCode);
    }

    protected function convertValidationExceptionToResponse(ValidationException $exception): JsonResponse
    {
        $errors = $exception->validator->errors()->getMessages();
        return $this->respondError(implode(', ', $errors), 422);
    }

    protected function convertAuthorizationExceptionToResponse(AuthorizationException $exception): JsonResponse
    {
        return $this->respondError($exception->getMessage(), 403);
    }
}
