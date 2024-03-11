## reposne

```php
<?php

use TaliumAbstract\Trait\ApiResponse;
use Illuminate\Http\Request;

class ExampleController
{
    use ApiResponse;

    public function notFoundExample()
    {
        return $this->respondNotFound();
    }

    public function successExample()
    {
        return $this->respondWithSuccess(['data' => 'Some data'], 'Request successful');
    }

    public function okExample()
    {
        return $this->respondOk();
    }

    public function unauthenticatedExample()
    {
        return $this->respondUnauthenticated();
    }

    public function forbiddenExample()
    {
        return $this->respondForbidden();
    }

    public function errorExample()
    {
        return $this->respondError('An error occurred');
    }

    public function createdExample()
    {
        return $this->respondCreated(['id' => 1]);
    }

    public function validationErrorExample(Request $request)
    {
        try {
            // Lakukan validasi
            $this->validate($request, [
                'name' => 'required|string',
                'email' => 'required|email',
            ]);
        } catch (ValidationException $e) {
            // Tangani pengecualian validasi dengan menggunakan metode dari trait ApiResponse
            return $this->convertValidationExceptionToResponse($e);
        }
    }

    public function noContentExample()
    {
        return $this->respondNoContent();
    }
}

```
