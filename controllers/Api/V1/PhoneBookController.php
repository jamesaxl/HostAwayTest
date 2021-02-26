<?php

namespace app\controllers\Api\V1;

use app\controllers\Controller;
use app\base\Request;
use app\schema\PhoneBook;

/**
 * In this part I felt tired
 * if you see something not 100% good
 * forgive me. I knew that I repeated some code
 *
 * Class PhoneBookController
 * @package app\controllers\Api\V1
 */

class PhoneBookController extends Controller
{
    /**
     * @return string
     */
    public function getAll(): string
    {
        $phoneBook = (new PhoneBook())->findAll();
        return $this->json([
            'error' => 0, 'data' => $phoneBook,
        ], 200);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function get(Request $request) :string
    {
        $params = $request->getParams();

        if (!isset($params['id']) || !is_numeric($params['id'])) {
            return $this->json([
                'error' => 1,
                'message' => 'Query string id is required',
            ], 400);
        }

        $phoneBook = (new PhoneBook())->find($params['id']);

        return $this->json([
            'error' => 0,
            'data' => $phoneBook
        ], 200);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function search(Request $request): string
    {
        $params = $request->getParams();

        if ( !$params['keyword']) {
            return $this->json([
                'error' => 1,
                'message' => 'Query string keyword is required',
            ], 400);
        }

        $phoneBooks = (new PhoneBook())->search($params['keyword']);

        return $this->json([
            'error' => 0, 'data' => $phoneBooks
        ], 200);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function store(Request $request): string
    {
        $phoneBook = new PhoneBook();
        $phoneBook->loadData($request->getBody());

        if (!$phoneBook->validateStore()) {
            return $this->json([
                'error' => 1,
                'message' => $phoneBook->getFirstError(),
            ], 400);
        }

        if ($phoneBook->register()) {
            return $this->json([
                'error' => 0,
                'message' => 'contact has been saved'
            ], 200);
        }

        return $this->json([
            'error' => 0,
            'message' => 'something wrong ty later'
        ], 400);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function update(Request $request): string
    {
        $params = $request->getParams();

        if (!isset($params['id']) || !is_numeric($params['id'])) {
            return $this->json([
                'error' => 1,
                'message' => 'Query string id is required',
            ], 400);
        }

        $phoneBook = new PhoneBook();
        $exist = $phoneBook->find($params['id']);

        if (!$exist) {
            return $this->json([
                'error' => 1,
                'message' => 'record with ID '.$params['id'].' not found'
            ], 400);
        }

        $phoneBook->loadData($request->getBody());

        if (!$phoneBook->validateUpdate()) {
            return $this->json([
                'error' => 1,
                'message' => $phoneBook->getFirstError(),
            ], 400);
        }

        if ($phoneBook->update($params['id'])) {
            return $this->json([
                'error' => 0,
                'message' => 'record with ID '.$params['id'].' has been updated',
            ], 200);
        }

        return $this->json([
            'error' => 1,
            'message' => 'something wrong please try later',
        ], 500);
    }

    /**
     * @param Request $request
     * @return string
     */
    public function delete(Request $request): string
    {
        $params = $request->getParams();

        if (!isset($params['id']) || !is_numeric($params['id'])) {
            return $this->json([
                'error' => 1,
                'message' => 'Query string id is required',
            ], 400);
        }

        if (!(new PhoneBook())->find($params['id'])) {
            return $this->json([
                'error' => 1, 'message' => 'record with ID '.$params['id'].' not found'
            ], 400);
        }

        if ((new PhoneBook())->delete($params['id'])) {
            return $this->json([
                'error' => 0,
                'message' => 'record with ID '.$params['id'].' has been deleted'
            ], 200);
        }

        return $this->json([
            'error' => 1,
            'message' => 'something wrong please try later',
        ], 500);
    }
}