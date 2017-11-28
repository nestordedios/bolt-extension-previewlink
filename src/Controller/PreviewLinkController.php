<?php

namespace Bolt\Extension\Bolt\PreviewLink\Controller;

use Bolt\Controller\Base;
use Bolt\Extension\Bolt\PreviewLink\Key\Key;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller class.
 *
 * @author Néstor de Dios Fernández <nestoru@twokings.nl>
 */
class PreviewLinkController extends Base
{
    /**
     * Specify which method handles which route.
     *
     * Base route/path is '/previewlink'
     *
     * {@inheritdoc}
     */
    public function addRoutes(ControllerCollection $ctr)
    {
        // /previewlink/{contenttype}/{id}
        $ctr->get('/{contenttypeslug}/{id}', [$this, 'record'])
            ->bind('previewlink');

        return $ctr;
    }

    /**
     * Controller for a single record preview page, like:
     * '/previewlink/{contenttype}/{id}?key=%242y%2410%2430leurY%2FE.fxLSX5kUzmIuLS6EencIJsx6E6SaxaJpS0gB4BQoP7C'.
     *
     * This function is overriden from Bolt\Controller\ Frontend.php
     *
     * @param Request $request         The request
     * @param string  $contenttypeslug The content type slug
     * @param string  $slug            The content slug
     *
     * @return TemplateResponse
     */
    public function record(Request $request, $contenttypeslug, $slug = '')
    {
        $contenttype = $this->getContentType($contenttypeslug);

        // If the ContentType is 'viewless', don't show the record page.
        if (isset($contenttype['viewless']) && $contenttype['viewless'] === true) {
            $this->abort(Response::HTTP_NOT_FOUND, "Page $contenttypeslug/$slug not found.");

            return null;
        }

        // Perhaps we don't have a slug. Let's see if we can pick up the 'id', instead.
        if (empty($slug)) {
            $slug = $request->get('id');
        }

        $slug = $this->app['slugify']->slugify($slug);

        // First, try to get it by slug and no matter the status.
        $content = $this->getContent($contenttype['slug'], ['slug' => $slug, 'returnsingle' => true, 'log_not_found' => !is_numeric($slug), 'status' => '!x']);

        if (is_numeric($slug) && (!$content || count($content) === 0)) {
            // And otherwise try getting it by ID and no matter the status
            $content = $this->getContent($contenttype['slug'], ['id' => $slug, 'returnsingle' => true, 'status' => '!x']);
        }

        // We check for the key. If the key is not correct, we abort.
        $app = $this->app;
        $key = new Key($app);
        $keyToCheck = $request->get('key');
        $keyValid = $key->checkKey($contenttype['slug'], $content->id, $keyToCheck);

        // No content or key not valid, no page!
        if (!$content || !$keyValid) {
            $this->abort(Response::HTTP_NOT_FOUND, "Page $contenttypeslug/$slug not found.");

            return null;
        }

        // Then, select which template to use, based on our 'cascading templates rules'
        $template = $this->templateChooser()->record($content);

        // Setting the editlink
        $this->app['editlink'] = $this->generateUrl('editcontent', ['contenttypeslug' => $contenttype['slug'], 'id' => $content->id]);
        $this->app['edittitle'] = $content->getTitle();

        // Make sure we can also access it as {{ page.title }} for pages, etc. We set these in the global scope,
        // So that they're also available in menu's and templates rendered by extensions.
        $globals = [
            'record'                      => $content,
            $contenttype['singular_slug'] => $content,
        ];

        return $this->render($template, [], $globals);
    }
































    /**
     * Handles GET requests on /example/url/in/controller
     *
     * @param Request $request
     *
     * @return Response
     */
    public function exampleUrl(Request $request)
    {
        $response = new Response('Hello, World!', Response::HTTP_OK);

        return $response;
    }

    /**
     * Handles GET requests on /example/url/json and return with json.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function exampleUrlJson(Request $request)
    {
        $jsonResponse = new JsonResponse();

        $jsonResponse->setData([
            'message' => 'I am a JSON response, yeah!',
        ]);

        return $jsonResponse;
    }

    /**
     * Handles GET requests on /example/url/parameter/{id} and return with json.
     *
     * @param Request $request
     * @param $id
     *
     * @return JsonResponse
     */
    public function exampleUrlWithParameter(Request $request, $id)
    {
        $jsonResponse = new JsonResponse();

        $jsonResponse->setData([
            'id' => $id,
        ]);

        return $jsonResponse;
    }

    /**
     * Handles GET requests on /example/url/get-parameter and return with some data as json.
     * Example: http://localhost/example/url/get-parameter?foo=bar&baz=foo&id=7
     * Works in the same way with POST requests
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function exampleUrlGetParameter(Request $request)
    {
        $jsonResponse = new JsonResponse();

        $jsonResponse->setData([
            'all' => $request->query->all(), // all GET parameter as key value array
            'id'  => $request->get('id'), // only 'id' GET parameter
        ]);

        return $jsonResponse;
    }

    /**
     * Handles GET requests on /example/url/template and return a template.
     *
     * @param Request $request
     *
     * @return string
     */
    public function exampleUrlTemplate(Application $app, Request $request)
    {
        return $this->render('example_site.twig', ['title' => 'Look at This Nice Template'], []);
    }
}
