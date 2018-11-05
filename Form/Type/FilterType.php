<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use KRG\DoctrineExtensionBundle\Entity\Sortable\SortableInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\RequestStack;
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

    /**
     * FilterType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $request
     */
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $event = $this->request->isMethod('GET') ? FormEvents::PRE_SET_DATA : FormEvents::PRE_SUBMIT;
        $builder->addEventListener($event, array($this, 'onPreSetData'));
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();
        $data = $event->getData() ?: [];

        if (isset($data['reset'])) {
            $event->setData([]);
            $data = [];
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $options['query_builder'];

        if ($queryBuilder instanceof \Closure) {
            $queryBuilder = $queryBuilder->call($this, $this->entityManager->getRepository($options['class']), $data);

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new \RuntimeException('Form option "query_builder" closure must return QueryBuilder instance');
            }
        }

        foreach ($options['fields'] as $field => $data) {
            $choices = $this->getChoices($queryBuilder, $data);

            $choices = array_flip($choices);

            if (count($choices) > 1) {
                $form->add(
                    $field, ChoiceType::class, [
                    'choices'     => $choices,
                    'placeholder' => $data['placeholder'] ?? $field,
                    'required'    => false,
                    'label'       => false,
                ]);
            } else {
                $form->add(
                    $field, HiddenType::class, [
                    'label' => false,
                ]);
            }
        }

        $form->add('reset', SubmitType::class);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $data
     *
     * @return array
     */
    protected function getChoices(QueryBuilder $queryBuilder, array $data)
    {
        // Handle multiple properties (ex: 'property' => ['width', 'height'])
        if (false === isset($data['property'])) {
            $_properties[] = 'name';
        } elseif (is_string($data['property'])) {
            $_properties[] = $data['property'];
        } elseif (is_array($data['property'])) {
            $_properties = $data['property'];
        }

        // Check properties existance
        $properties = [];
        $reflectionClass = $this->entityManager->getClassMetadata($data['class'])->getReflectionClass();
        foreach ($_properties as $property) {
            if ($reflectionClass->hasProperty($property)) {
                $properties[] = $property;
            }
        }

        $queryBuilder = (clone $queryBuilder)
            ->resetDQLPart('select')
            ->resetDQLPart('groupBy')
            ->resetDQLPart('orderBy')
            ->select(
                sprintf('MAX(%s.id) HIDDEN _', $queryBuilder->getRootAliases()[0]),
                sprintf('%s.id', $data['alias'])
            )
            ->groupBy(sprintf('%s.id', $data['alias']));

        foreach ($properties as $property) {
            $orderBy = $reflectionClass->implementsInterface(SortableInterface::class) ? 'position' : $property;
            $queryBuilder
                ->addSelect(sprintf('%s.%s', $data['alias'], $property))
                ->orderBy(sprintf('%s.%s', $data['alias'], $orderBy), 'ASC');
        }

        $results = $queryBuilder
            ->getQuery()
            ->getArrayResult();

        if (count($properties)) {
            $unique = isset($data['unique']) && $data['unique'];

            $choices = [];
            foreach ($results as $row) {
                $value = '';
                foreach ($properties as $property) {
                    // Concat properties values and delimiter (ex: 100 x 300)
                    if ($row[$property]) {
                        $value .= $row[$property];
                        if ($property !== end($properties)) {
                            $value .= $data['property_delimiter'] ?? '';
                        }
                    } else {
                        $value = $data['placeholder'];
                    }
                }
                $choices[$row[$unique ? $properties[0] : 'id']] = $value;
            }
        }

        $choices = array_map(
            function ($choice) {
                return $choice ?: 'None';
            }, $choices
        );

        return $choices;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array('csrf_protection' => false));
        $resolver->setRequired(['class', 'query_builder', 'fields']);
        $resolver->setAllowedTypes('query_builder', array(QueryBuilder::class, \Closure::class));
        $resolver->setAllowedTypes('class', 'string');
        $resolver->setAllowedTypes('fields', 'array');
    }
}
