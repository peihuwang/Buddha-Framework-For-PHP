<?php
/*
初始化工厂对象
$factory = new Buddha_Validator_Factory(new Buddha_Validator_Translator);

//验证
$rules = array(
    'username' => 'required|min:5',
    'password' => 'required|min:3|confirmed',
    'password_confirmation' => 'required|min:3'
);

$input = array(
    'username' => '12345',
    'password' => '222222',
    'password_confirmation' => '222222'
);
$validator = $factory->make($input, $rules);

//判断验证是否通过
if ($validator->passes()) {
    echo 'pass';
    //通过
} else {
    //未通过
    //输出错误消息
    print_r($validator->messages()); // 或者 $validator->errors();
}
*/
class Buddha_Validator_Factory extends Buddha_Base_Component
{
    /**
     * The Translator implementation.
     *
     * @var Buddha_Validator_TranslatorInterface
     */
    protected $translator;

    /**
     * The Presence Verifier implementation.
     *
     * @var Buddha_Validator_TranslatorInterface
     */
    protected $verifier;

    /**
     * All of the custom validator extensions.
     *
     * @var array
     */
    protected $extensions = array();

    /**
     * All of the custom implicit validator extensions.
     *
     * @var array
     */
    protected $implicitExtensions = array();

    /**
     * All of the custom validator message replacers.
     *
     * @var array
     */
    protected $replacers = array();

    /**
     * All of the fallback messages for custom rules.
     *
     * @var array
     */
    protected $fallbackMessages = array();

    /**
     * The Validator resolver instance.
     *
     * @var Closure
     */
    protected $resolver;

    /**
     * Create a new Validator factory instance.
     *
     * @param Buddha_Validator_TranslatorInterface $translator
     *
     * @return Buddha_Validator_Factory
     */
    public function __construct(Buddha_Validator_TranslatorInterface $translator = null)
    {
        parent::__construct();
        $this->translator = $translator ?: new Buddha_Validator_Translator();
    }

    /**
     * Create a new Validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     *
     * @return Buddha_Validator_Validator
     */
    public function make(array $data, array $rules, array $messages = array(), array $customAttributes = array())
    {
        // The presence verifier is responsible for checking the unique and exists data
        // for the validator. It is behind an interface so that multiple versions of
        // it may be written besides database. We'll inject it into the validator.
        $validator = $this->resolve($data, $rules, $messages, $customAttributes);

        if (!is_null($this->verifier)) {
            $validator->setPresenceVerifier($this->verifier);
        }

        $this->addExtensions($validator);

        return $validator;
    }

    /**
     * Add the extensions to a validator instance.
     *
     * @param Budha_Validator_Validator $validator
     */
    protected function addExtensions(Buddha_Validator_Validator $validator)
    {
        $validator->addExtensions($this->extensions);

        // Next, we will add the implicit extensions, which are similar to the required
        // and accepted rule in that they are run even if the attributes is not in a
        // array of data that is given to a validator instances via instantiation.
        $implicit = $this->implicitExtensions;

        $validator->addImplicitExtensions($implicit);

        $validator->addReplacers($this->replacers);

        $validator->setFallbackMessages($this->fallbackMessages);
    }

    /**
     * Resolve a new Validator instance.
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     *
     * @return Budha_Validator_Validator
     */
    protected function resolve(array $data, array $rules, array $messages, array $customAttributes)
    {
        if (is_null($this->resolver)) {
            return new Buddha_Validator_Validator($this->translator, $data, $rules, $messages, $customAttributes);
        } else {
            return call_user_func($this->resolver, $this->translator, $data, $rules, $messages, $customAttributes);
        }
    }

    /**
     * Register a custom validator extension.
     *
     * @param string $rule
     * @param \Closure|string $extension
     * @param string $message
     */
    public function extend($rule, $extension, $message = null)
    {
        $this->extensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[Buddha_Validator_Helpers::snake_case($rule)] = $message;
        }
    }

    /**
     * Register a custom implicit validator extension.
     *
     * @param string $rule
     * @param \Closure|string $extension
     * @param string $message
     */
    public function extendImplicit($rule, $extension, $message = null)
    {
        $this->implicitExtensions[$rule] = $extension;

        if ($message) {
            $this->fallbackMessages[Buddha_Validator_Helpers::snake_case($rule)] = $message;
        }

    }

    /**
     * Register a custom implicit validator message replacer.
     *
     * @param string $rule
     * @param \Closure|string $replacer
     */
    public function replacer($rule, $replacer)
    {
        $this->replacers[$rule] = $replacer;
    }

    /**
     * Set the Validator instance resolver.
     *
     * @param \Closure $resolver
     */
    public function resolver(Closure $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Get the Translator implementation.
     *
     * @return Buddha_Validator_TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Get the Presence Verifier implementation.
     *
     * @return Buddha_Validator_PresenceVerifierInterface
     */
    public function getPresenceVerifier()
    {
        return $this->verifier;
    }

    /**
     * Set the Presence Verifier implementation.
     *
     * @param Buddha_Validator_PresenceVerifierInterface $presenceVerifier
     */
    public function setPresenceVerifier(Buddha_Validator_PresenceVerifierInterface $presenceVerifier)
    {
        $this->verifier = $presenceVerifier;
    }

}