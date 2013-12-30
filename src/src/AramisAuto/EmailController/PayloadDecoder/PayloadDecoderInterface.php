<?php
namespace AramisAuto\EmailController\PayloadDecoder;

interface PayloadDecoderInterface
{
    public function decode($payload);
}
