<?php

declare(strict_types=1);

namespace App\Form;

use App\Model\CancelBid;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CancelBidType extends AbstractCancelType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CancelBid::class,
        ]);
    }
}
