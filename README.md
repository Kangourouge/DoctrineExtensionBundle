# DoctrineExtensionBundle
Deal with interfaces

### PhoneNumberType

```twig
{% block javascripts %}
    {{ parent() }}
    <script src="{{ asset('bundles/krgdoctrineextension/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('bundles/krgdoctrineextension/plugin/intl-tel-input/js/intlTelInput.min.js') }}"></script>
    <script src="{{ asset('bundles/krgdoctrineextension/plugin/intl-tel-input/js/utils.js') }}"></script>
{% endblock %}
```

International Telephone Input options usage:
https://github.com/jackocnr/intl-tel-input#options

```php
$builder->add('phone', PhoneNumberType::class, ['options' => ['only_countries' => ['fr']]]);
```

