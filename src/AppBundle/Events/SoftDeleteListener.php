<?php

namespace AppBundle\Events;

use AppBundle\Entity\Groups;
use AppBundle\Entity\Users;
use Doctrine\ORM\Event\LifecycleEventArgs;

class SoftDeleteListener
{
    public function preSoftDelete(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();
        $entityManager = $event->getEntityManager();

        if ($entity instanceof Users) {
            if ( strpos($entity->getEmail(), '_/...deleted.../_') === false ) {
                $entity->setEmail($entity->getEmail() . '_/...deleted.../_' . time());
                $entityManager->persist($entity);
                $entityManager->flush();
            }
        } elseif ($entity instanceof Groups) {
            if ( strpos($entity->getNumber(), '_/...deleted.../_') === false ) {
                $entity->setNumber($entity->getNumber() . '_/...deleted.../_' . time());
                $entityManager->persist($entity);
                $entityManager->flush();
            }
        }
    }
}