<?php

/*
 * Copyright 2015 Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Cloud\Samples\Bookshelf\DataModel;

use Google\Cloud\Datastore\DatastoreClient;
use Google\Cloud\Datastore\Entity;

/**
 * Class Datastore implements the DataModel with a Google Data Store.
 */
class Datastore implements DataModelInterface
{
    private $datasetId;
    private $datastore;
    protected $columns = [
        'id'            => 'integer',
        'name'         => 'string',
    ];

    public function __construct($projectId)
    {
        $this->datasetId = $projectId;
        $this->datastore = new DatastoreClient([
            'projectId' => $projectId,
        ]);
    }

    public function listBooks($limit = 10, $cursor = null)
    {
        $query = $this->datastore->query()
            ->kind('SKU')
            ->order('name')
            ->limit($limit)
            ->start($cursor);

        $results = $this->datastore->runQuery($query);

        $books = [];
        $nextPageCursor = null;
        foreach ($results as $entity) {
            $book = $entity->get();
            $book['name'] = $entity->key()->pathEndIdentifier();
            $books[] = $book;
            $nextPageCursor = $entity->cursor();
        }

        return [
            'books' => $books,
            'cursor' => $nextPageCursor,
        ];
    }

     public function queryListBooks($queryval, $limit = 10, $cursor = null)
        {
            $query = $this->datastore->query()
                ->kind('SKU')
                ->filter('name', '>=', $queryval)
                ->filter('name', '<', $upperlimit)
                ->order('name')
                ->limit($limit)
                ->start($cursor);

            $results = $this->datastore->runQuery($query);

            $books = [];
            $nextPageCursor = null;
            foreach ($results as $entity) {
                $book = $entity->get();
                $book['name'] = $entity->key()->pathEndIdentifier();
                $books[] = $book;
                $nextPageCursor = $entity->cursor();
            }

            return [
                'books' => $books,
                'cursor' => $nextPageCursor,
            ];
        }

    public function queryBooks($queryval, $limit = 20, $cursor = null)
    {
        /**$matches_string = "";*/
        $products = array();

        try
        {
            //cache miss, let us go to datastore and fetch the result...
            $upperlimit = $queryval . json_decode('"\ufffd"');
            $query = $this->datastore->query()
                ->kind('SKU')
                ->filter('name', '>=', $queryval)
                ->filter('name', '<', $upperlimit)
                ->order('name')
                ->limit($limit);
            $result = $this->datastore->runQuery($query);
            foreach ($result as $SKU) {
                /**$matches_string = $matches_string . $SKU['name'] . "<br/>";*/
                $products[] = $SKU['name'];
            }
            return $products;
        }
        catch (Exception $e) {
        	echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    public function create($book, $key = null)
    {
        $this->verifyBook($book);

        $key = $this->datastore->key('SKU');
        $entity = $this->datastore->entity($key, $book);

        $this->datastore->insert($entity);

        // return the ID of the created datastore entity
        return $entity->key()->pathEndIdentifier();
    }

    public function read($id)
    {
        $key = $this->datastore->key('SKU', $id);
        $entity = $this->datastore->lookup($key);

        if ($entity) {
            $book = $entity->get();
            $book['name'] = $id;
            return $book;
        }

        return false;
    }

    public function update($book)
    {
        $this->verifyBook($book);

        if (!isset($book['name'])) {
            throw new \InvalidArgumentException('Book must have an "id" attribute');
        }

        $transaction = $this->datastore->transaction();
        $key = $this->datastore->key('SKU', $book['id']);
        $task = $transaction->lookup($key);
        unset($book['id']);
        $entity = $this->datastore->entity($key, $book);
        $transaction->upsert($entity);
        $transaction->commit();

        // return the number of updated rows
        return 1;
    }

    public function delete($id)
    {
        $key = $this->datastore->key('SKU', $id);
        return $this->datastore->delete($key);
    }

    private function verifyBook($book)
    {
        if ($invalid = array_diff_key($book, $this->columns)) {
            throw new \InvalidArgumentException(sprintf(
                'unsupported book properties: "%s"',
                implode(', ', $invalid)
            ));
        }
    }
}
