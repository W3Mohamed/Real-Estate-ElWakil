<?php

namespace App\Admin\Field;

use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\FieldTrait;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class MapField implements FieldInterface
{
    use FieldTrait;

    public const OPTION_CENTER_LAT = 'centerLat';
    public const OPTION_CENTER_LNG = 'centerLng';
    public const OPTION_ZOOM = 'zoom';

    public static function new(string $propertyName, ?string $label = null): self
    {
        return (new self())
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setTemplateName('crud/field/map')
            ->setTemplatePath('admin/crud/field/map.html.twig')
            ->setFormType(\App\Form\Type\MapType::class)
            ->addCssClass('field-map')
            ->setCustomOption(self::OPTION_CENTER_LAT, 48.8566)
            ->setCustomOption(self::OPTION_CENTER_LNG, 2.3522)
            ->setCustomOption(self::OPTION_ZOOM, 10);
    }

    public function setCenterLat(float $lat): self
    {
        $this->setCustomOption(self::OPTION_CENTER_LAT, $lat);
        return $this;
    }

    public function setCenterLng(float $lng): self
    {
        $this->setCustomOption(self::OPTION_CENTER_LNG, $lng);
        return $this;
    }

    public function setZoom(int $zoom): self
    {
        $this->setCustomOption(self::OPTION_ZOOM, $zoom);
        return $this;
    }
}