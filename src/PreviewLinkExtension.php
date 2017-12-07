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
            ->setFileName('extension.js')
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

        if ($id) {
            $url = $app['url_generator']->generate('previewlink', [
                'contenttypeslug' => $contenttypeslug,
                'id' => $id,
            ]);

            $unpublishedRecord = $app['query']->getContent($contenttypeslug, ['id' => $id, 'status' => '!published']);
        } else {
            $url = null;
            $unpublishedRecord = null;
        }

        if($url && $unpublishedRecord->count() > 0){
            return $this->renderTemplate('preview_link_widget.twig', [
                'url' => $url . '?key=' . urlencode($key->getKey($contenttypeslug, $id)),
            ]);
        }

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

}
