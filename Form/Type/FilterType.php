<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FilterType
 */
class FilterType extends AbstractType
{

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var RequestStack */
    private $request;

    /** @var SessionInterface */
    private $session;

    /**
     * FilterType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
     * @param SessionInterface $session
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, SessionInterface $session)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->session = $session;
    }

    /**
     * @return string
     */
    public function getSessionKey()
    {
        return sprintf('Filter/%s%s', $this->getBlockPrefix(), $this->request->getPathInfo());
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->request->isMethod('GET')) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, array($this, 'onPreSetData'));
        } else {
            $builder->addEventListener(FormEvents::PRE_SUBMIT, array($this, 'onPreSubmit'));
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData() ?: $this->session->get($this->getSessionKey(), []);
        $event->setData($data);

        $this->handle($event);
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        if (isset($data['reset'])) {
            $fields = $event->getForm()->getConfig()->getOption('fields');
            foreach ($fields as $name => $field) {
                if ($field['type'] !== 'integer') {
                    unset($data[$name]);
                } else {
                    unset($data[$name][$name.'_from']);
                    unset($data[$name][$name.'_to']);
                }
            }
        }

        $event->setData($data);

        $this->session->set($this->getSessionKey(), $data);

        $this->handle($event);
    }

    /**
     * @param FormEvent $event
     */
    public function handle(FormEvent $event)
    {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();
        $data = $event->getData();

        $fields = $event->getForm()->getConfig()->getOption('fields');
        foreach ($data as $key => &$value) {
            if (isset($fields[$key]) && $fields[$key]['type'] === 'boolean') {
                $value = is_numeric($value) ? (bool)(int)$value : null;
            }
        }
        unset($value);

        $fields = $event->getForm()->getConfig()->getOption('fields');

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $options['query_builder'];
        if ($queryBuilder instanceof \Closure) {
            $minimal = $event->getForm()->getConfig()->getOption('minimal');
            $data = $minimal ? $data : [];

            $queryBuilder = $queryBuilder->call($this, $this->entityManager->getRepository($options['class']), $data);

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new \RuntimeException('Form option "query_builder" closure must return QueryBuilder instance');
            }
        }

        $rows = $this->getRows($queryBuilder, $options['fields']);

        $this->addChoices($form, $rows, $data, $options);

        $form->add('reset', SubmitType::class);
    }

    /**
     * @param FormInterface $form
     * @param array $rows
     * @param array $data
     * @param array $options
     */
    protected function addChoices(FormInterface $form, array $rows, array $data, array $options)
    {
        foreach ($options['fields'] as $field => $config) {
            if (count($rows[$field]) > 0 || isset($data[$field])) {
                $isEnum = strpos($config['type'], '_enum') === strlen($config['type']) - strlen('_enum');

                $options = [];
                if (!$form->getConfig()->getOption('label')) {
                    $options['label'] = false;
                }

                if ($config['type'] && $config['type'] == 'integer') {
                    $options = array_replace_recursive(
                        [
                            'data' => $rows[$field],
                        ],
                        $options
                    );

                    $form->add($field, MinMaxRangeType::class, $options);
                } else {
                    $options = array_replace_recursive(
                        [
                            'choices' => $rows[$field],
                            'choice_translation_domain' => $isEnum ? $config['choice_translation_domain'] ?? $config['type'] : null,
                            'data' => $data[$field] ?? null,
                            'placeholder' => strtoupper($field),
                            'multiple' => $form->getConfig()->getOption('multiple'),
                            'required' => false
                        ],
                        $options,
                        $config['options'] ?? []
                    );

                    $form->add($field, ChoiceType::class, $options);
                }
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $fields
     *
     * @return array
     */
    protected function getRows(QueryBuilder $queryBuilder, array $fields)
    {
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder = (clone $queryBuilder)
            ->distinct()
            ->resetDQLPart('select')
            ->resetDQLPart('orderBy');


        foreach ($fields as $field => $config) {
            $alias = $config['alias'] ?? $rootAlias;
            foreach ($config['select'] as $property => $name) {
                $queryBuilder->addSelect(sprintf('%s.%s as %s', $alias, $property, $name));
            }
        }

        $results = $queryBuilder->getQuery()->getArrayResult();

        $rows = [];

        foreach ($fields as $field => $config) {

            $_rows = [];
            $identifier = $config['select'][$config['identifier']];

            foreach ($results as $data) {
                $select = array_flip($config['select']);

                $idx = $data[$identifier] ?? $config['empty_value'];

                if (!isset($_rows[$idx])) {
                    $_rows[$idx] = array_combine($select, array_intersect_key($data, $select));
                }
            }

            $orderBy = $config['orderBy'];

            usort($_rows, function ($row1, $row2) use ($orderBy) {
                if ($row1[$orderBy] === null) {
                    return -1;
                }
                if ($row2[$orderBy] === null) {
                    return 1;
                }
                if ($row1[$orderBy] === $row2[$orderBy]) {
                    return 0;
                }

                return $row1[$orderBy] > $row2[$orderBy] ? 1 : -1;
            });

            $rows[$field] = [];
            $properties = array_flip($config['properties']);
            $identifier = $config['identifier'];
            foreach ($_rows as $row) {
                if ($row[$identifier] === null) {
                    $rows[$field][$config['empty_label']] = $config['empty_value'];
                } else if (is_bool($row[$identifier])) {
                    $rows[$field][$row[$identifier] ? 'Yes' : 'No'] = (bool)(int)$row[$identifier];
                }  else if ($config['type'] == 'integer') {
                    $args = array_intersect_key($row, $properties);
                    foreach ($args as $property => $value) {
                        if (!isset($rows[$field]['min']) || $value < $rows[$field]['min']) {
                            $rows[$field]['min'] = $value;
                        }
                        if (!isset($rows[$field]['max']) || $value > $rows[$field]['max']) {
                            $rows[$field]['max'] = $value;
                        }
                    }

                } else {
                    $args = array_intersect_key($row, $properties);
                    array_unshift($args, $config['format']);

                    $value = call_user_func_array('sprintf', $args);

                    $rows[$field][$value] = $row[$identifier];
                }
            }
        }

        return $rows;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['class', 'query_builder', 'fields']);
        $resolver->setDefined(['multiple', 'session', 'minimal']);
        $resolver->setAllowedTypes('query_builder', array(QueryBuilder::class, \Closure::class));
        $resolver->setAllowedTypes('class', 'string');
        $resolver->setAllowedTypes('fields', 'array');
        $resolver->setAllowedTypes('label', 'boolean');
        $resolver->setAllowedTypes('multiple', 'boolean');
        $resolver->setAllowedTypes('session', 'boolean');
        $resolver->setAllowedTypes('minimal', 'boolean');

        $resolver->setDefaults(['multiple' => false, 'minimal' => true, 'session' => true, 'label' => false]);

        $resolver->setNormalizer('fields', function (Options $options, array $fields) {
            $classMetadata = $this->entityManager->getClassMetadata($options->offsetGet('class'));

            foreach ($fields as $field => &$config) {
                $isAssociation = $classMetadata->hasAssociation($field) || isset($config['alias']);
                $type = $classMetadata->getTypeOfField($field);

                $isScalar = !$isAssociation && ($type === 'boolean' || substr($type, -5) === '_enum');

                $config = array_merge(
                    [
                        'properties' => [$isScalar ? $field : 'name'],
                        'format' => '%1$s',
                        'empty_value' => 0,
                        'empty_label' => 'None',
                        'identifier' => $isScalar ? $field : 'id',
                        'orderBy' => $isScalar ? $field : 'name',
                        'alias' => $isAssociation ? $field : null,
                        'required' => false,
                        'type' => $type
                    ], $config
                );

                $select = array_unique(array_merge($config['properties'], [$config['orderBy']], [$config['identifier']]));

                $config['select'] = [];
                foreach ($select as $property) {
                    $config['select'][$property] = sprintf('%s_%s', $field, $property);
                }
            }
            unset($config);

            return $fields;
        });
    }
}
