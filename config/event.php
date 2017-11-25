<?php

//composer require symfony/event-dispatcher

$app['events'] = function () {
    $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();

    $listeners = [
        // 'Listener\TestNotification'
    ];

    foreach ($listeners as $listener) {
        $dispatcher->addSubscriber(new $listener);
    }

    return $dispatcher;
};


//<?php
//
//namespace Event;
//
//use Symfony\Component\EventDispatcher\Event;
//
//class TestEvent extends Event
//{
//    public $data;
//
//    public function __construct($data)
//    {
//        $this->data = $data;
//    }
//
//    public static function className()
//    {
//        return get_called_class();
//    }
//}


//<?php
//
//namespace Listener;
//
//use Event\TestEvent;
//use Symfony\Component\EventDispatcher\EventSubscriberInterface;
//
//class TestNotification implements EventSubscriberInterface
//{
//
//    /**
//     * Returns an array of event names this subscriber wants to listen to.
//     *
//     * The array keys are event names and the value can be:
//     *
//     *  * The method name to call (priority defaults to 0)
//     *  * An array composed of the method name to call and the priority
//     *  * An array of arrays composed of the method names to call and respective
//     *    priorities, or 0 if unset
//     *
//     * For instance:
//     *
//     *  * array('eventName' => 'methodName')
//     *  * array('eventName' => array('methodName', $priority))
//     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
//     *
//     * @return array The event names to listen to
//     */
//    public static function getSubscribedEvents()
//    {
//        return [
//            TestEvent::className() => 'test',
//        ];
//    }
//
//    //$app['events']->dispatch('Event\\TestEvent', new \Event\TestEvent(1));
//    public function test(TestEvent $event)
//    {
//        var_dump($event->data);
//    }
//}