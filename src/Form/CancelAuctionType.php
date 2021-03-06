<?php

declare(strict_types=1);

namespace App\Form;

use App\Model\CancelAuction;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CancelAuctionType extends AbstractCancelType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CancelAuction::class,
        ]);
    }
}
