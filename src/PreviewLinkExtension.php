<?php

namespace Bolt\Extension\Bolt\PreviewLink;

use Bolt\Asset\File\JavaScript;
use Bolt\Asset\Target;
use Bolt\Asset\Widget\Widget;
use Bolt\Controller\Zone;
use Bolt\Extension\SimpleExtension;
use Bolt\Extension\Bolt\PreviewLink\Controller\PreviewLinkController;
use Bolt\Extension\Bolt\PreviewLink\Key\Key;
use Silex\Application;
use Silex\ControllerCollection;

/**
 * PreviewLink extension class.
 *
 * @author Néstor de Dios Fernández <nestor@twokings.nl>
 */
class PreviewLinkExtension extends SimpleExtension
{

    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
        $app = $this->getContainer();

        $previeLinkJavaScript = JavaScript::create()
        ->setFileName('/theme/base-2016/js/extension.js')
        ->setLate(true)
        ->setPriority(5)
        ->setAttributes(['defer', 'async'])
        ->setZone(Zone::BACKEND)
        ;

        $previewLinkWidget = Widget::create()
        ->setZone(Zone::BACKEND)
        ->setLocation(Target::WIDGET_BACK_EDITCONTENT_ASIDE_MIDDLE)
        ->setCallback([$this, 'callbackWidget'])
        ->setCallbackArguments(['app' => $app])
        ->setDefer(false)
        ->setPriority(5)
        ;

        return [
            $previewLinkWidget, $previeLinkJavaScript
        ];
    }

    /**
     * The callback function for the preview link Widget.
     *
     * @return string
     */
    public function callbackWidget(Application $app)
    {
        $previewLinkConfig = $app['previewlink.config'];

        $id = $app['request']->get('id');
        $contenttypeslug = $app['request']->get('contenttypeslug');
        $key = new Key($app);

        $url = $app['url_generator']->generate('previewlink', [
            'contenttypeslug' => $contenttypeslug,
            'id' => $id,
        ]);

        return $this->renderTemplate('preview_link_widget.twig', [
            'url' => $url . '?key=' . urlencode($key->getKey($contenttypeslug, $id)),
        ]);
    }

    /**
     * We can share our configuration as a service so our other classes can use it.
     *
     * {@inheritdoc}
     */
    protected function registerServices(Application $app)
    {
        $app['previewlink.config'] = $app->share(function ($app) {
            return $this->getConfig();
        });
    }

    /**
     * {@inheritdoc}
     */
    // public function getServiceProviders()
    // {
    //     return [
    //         $this,
    //         new Provider\PreviewLinkProvider(),
    //     ];
    // }

    /**
     * {@inheritdoc}
     *
     * Mount the ExampleController class to all routes that match '/example/url/*'
     *
     * To see specific bindings between route and controller method see 'connect()'
     * function in the ExampleController class.
     */
    protected function registerFrontendControllers()
    {
        return [
            '/previewlink' => new PreviewLinkController(),
        ];
    }





























    /**
     * {@inheritdoc}
     *
     * This first route will be handled in this extension class,
     * then we switch to an extra controller class for the routes.
     */
    protected function registerFrontendRoutes(ControllerCollection $collection)
    {
        $collection->match('/example/url', [$this, 'routeExampleUrl']);
    }

    /**
     * Handles GET requests on the /example/url route.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function routeExampleUrl(Request $request)
    {
        $response = new Response('Hello, Bolt!', Response::HTTP_OK);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendRoutes(ControllerCollection $collection)
    {
        $collection->match('/extend/my-custom-backend-page-route', [$this, 'exampleBackendPage']);
    }

    /**
     * Handles GET requests on /bolt/my-custom-backend-page and return a template.
     *
     * @param Request $request
     *
     * @return string
     */
    public function exampleBackendPage(Request $request)
    {
        $html = $this->renderTemplate('custom_backend_site.twig', ['title' => 'My Custom Page']);

        return new Markup($html, 'UTF-8');
    }
}
