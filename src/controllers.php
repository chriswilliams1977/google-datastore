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

namespace Google\Cloud\Samples\Bookshelf;

/*
 * Adds all the controllers to $app.  Follows Silex Skeleton pattern.
 */
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Google\Cloud\Samples\Bookshelf\DataModel\DataModelInterface;
use Google\Cloud\Samples\Bookshelf\FileSystem\CloudStorage;

// If user goes to root redirect to products dir
$app->get('/', function (Request $request) use ($app) {
    return $app->redirect('/products/');
});

// [START index] user navigates to /products
$app->get('/products/', function (Request $request) use ($app) {
    $twig = $app['twig'];
    return $twig->render('list.html.twig');
});
// [END index]

// [START query]
$app->get('/products/{searchtext}', function ($searchtext) use ($app) {

    echo 'Matches:<br/>';
    $result = "";

    //Get the string typed in by user for autocompletion...
    $queryval = strtolower($searchtext);

    //If it is not blank...
    if ($queryval[0]) {

        /** @var DataModelInterface $model */
        $model = $app['bookshelf.model'];

        //cache miss, let us go to datastore and fetch the result...
        $upperlimit = $queryval . json_decode('"\ufffd"');
        $result = $model->queryBooks($queryval);
    }
    return $result;

});
// [END query]

// [START show individual]
$app->get('/products/{id}', function ($id) use ($app) {
    /** @var DataModelInterface $model */
    $model = $app['bookshelf.model'];
    $book = $model->read($id);
    if (!$book) {
        return new Response('', Response::HTTP_NOT_FOUND);
    }
    /** @var Twig_Environment $twig */
    $twig = $app['twig'];

    return $twig->render('view.html.twig', array('book' => $book));
});
// [END show]

// [START show list]
$app->post('/products/{searchtext}', function ($searchtext) use ($app) {
    /** @var DataModelInterface $model */
   $model = $app['bookshelf.model'];

   /** @var Twig_Environment $twig */
   $twig = $app['twig'];

   $token = $request->query->get('page_token');
   $bookList = $model->queryListBooks($searchtext, $app['bookshelf.page_size'], $token);

   return $twig->render('list.html.twig', array(
       'books' => $bookList['books'],
       'next_page_token' => $bookList['cursor'],
   ));
});
// [END show]








