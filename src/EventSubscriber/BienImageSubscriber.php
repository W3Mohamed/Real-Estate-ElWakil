<?php
namespace App\EventSubscriber;

use App\Entity\Bien;
use App\Entity\Images;
use App\Service\ImageUploader;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class BienImageSubscriber implements EventSubscriberInterface
{
    private $uploader;
    private $em;

    public function __construct(ImageUploader $uploader, EntityManagerInterface $em)
    {
        $this->uploader = $uploader;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityPersistedEvent::class => ['uploadImages'],
            BeforeEntityUpdatedEvent::class => ['uploadImages'],
        ];
    }

    public function uploadImages($event)
    {
        $entity = $event->getEntityInstance();

        if (!($entity instanceof Bien)) {
            return;
        }

        $images = $entity->getImages();

        foreach ($images as $image) {
            $imageFile = $image->getImageFile();

            if ($imageFile instanceof UploadedFile) {
                $fileName = $this->uploader->upload($imageFile);
                $image->setImage($fileName);
                $image->setBien($entity);
                $this->em->persist($image);
            }
        }
    }
}