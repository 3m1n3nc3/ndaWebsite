<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\TwiML\Voice;

use Twilio\TwiML\TwiML;

class Say extends TwiML {
    /**
     * Say constructor.
     * 
     * @param string $message Message to say
     * @param array $attributes Optional attributes
     */
    public function __construct($message, $attributes = array()) {
        parent::__construct('Say', $message, $attributes);
    }

    /**
     * Add Break child.
     * 
     * @param array $attributes Optional attributes
     * @return TwiML Child element.
     */
    public function break_($attributes = array()) {
        return $this->nest(new Voice\SsmlBreak($attributes));
    }

    /**
     * Add Emphasis child.
     * 
     * @param string $words Words to emphasize
     * @param array $attributes Optional attributes
     * @return TwiML Child element.
     */
    public function emphasis($words, $attributes = array()) {
        return $this->nest(new Voice\SsmlEmphasis($words, $attributes));
    }

    /**
     * Add P child.
     * 
     * @param string $words Words to speak
     * @return TwiML Child element.
     */
    public function p($words) {
        return $this->nest(new Voice\SsmlP($words));
    }

    /**
     * Add Phoneme child.
     * 
     * @param string $words Words to speak
     * @param array $attributes Optional attributes
     * @return TwiML Child element.
     */
    public function phoneme($words, $attributes = array()) {
        return $this->nest(new Voice\SsmlPhoneme($words, $attributes));
    }

    /**
     * Add Prosody child.
     * 
     * @param string $words Words to speak
     * @param array $attributes Optional attributes
     * @return TwiML Child element.
     */
    public function prosody($words, $attributes = array()) {
        return $this->nest(new Voice\SsmlProsody($words, $attributes));
    }

    /**
     * Add S child.
     * 
     * @param string $words Words to speak
     * @return TwiML Child element.
     */
    public function s($words) {
        return $this->nest(new Voice\SsmlS($words));
    }

    /**
     * Add Say-As child.
     * 
     * @param string $words Words to be interpreted
     * @param array $attributes Optional attributes
     * @return TwiML Child element.
     */
    public function say_As($words, $attributes = array()) {
        return $this->nest(new Voice\SsmlSayAs($words, $attributes));
    }

    /**
     * Add Sub child.
     * 
     * @param string $words Words to be substituted
     * @param array $attributes Optional attributes
     * @return TwiML Child element.
     */
    public function sub($words, $attributes = array()) {
        return $this->nest(new Voice\SsmlSub($words, $attributes));
    }

    /**
     * Add W child.
     * 
     * @param string $words Words to speak
     * @param array $attributes Optional attributes
     * @return TwiML Child element.
     */
    public function w($words, $attributes = array()) {
        return $this->nest(new Voice\SsmlW($words, $attributes));
    }

    /**
     * Add Voice attribute.
     * 
     * @param say:Enum:Voice $voice Voice to use
     * @return TwiML $this.
     */
    public function setVoice($voice) {
        return $this->setAttribute('voice', $voice);
    }

    /**
     * Add Loop attribute.
     * 
     * @param integer $loop Times to loop message
     * @return TwiML $this.
     */
    public function setLoop($loop) {
        return $this->setAttribute('loop', $loop);
    }

    /**
     * Add Language attribute.
     * 
     * @param say:Enum:Language $language Message langauge
     * @return TwiML $this.
     */
    public function setLanguage($language) {
        return $this->setAttribute('language', $language);
    }
}