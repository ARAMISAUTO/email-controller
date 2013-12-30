<?php
namespace ConstructionsIncongrues\EmailController\PayloadDecoder;

interface PayloadDecoderInterface
{
    public function decode($payload);
}
