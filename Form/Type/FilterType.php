<?php

namespace KRG\DoctrineExtensionBundle\Form\Type;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FilterType
 * @package KRG\DoctrineExtensionBundle\Form\Type
 */
class FilterType extends AbstractType
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Request
     */
    protected $request;

    /**
     * FilterType constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param RequestStack $requestStack
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
    public function onPreSetData(FormEvent $event) {

        $form = $event->getForm();
        $options = $form->getConfig()->getOptions();
        $data = $event->getData() ?: array();

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $options['query_builder'];

        if ($queryBuilder instanceof \Closure) {
            $queryBuilder = $queryBuilder->call($this, $this->entityManager->getRepository($options['class']), $data);

            if (!$queryBuilder instanceof QueryBuilder) {
                throw new \RuntimeException('Form option "query_builder" closure must return QueryBuilder instance');
            }
        }

        foreach($options['fields'] as $field => $data) {
            $choices = $this->getChoices($queryBuilder, $data);
            $form->add($field, ChoiceType::class, array(
                'choices' => $choices,
                'required' => false,
                'placeholder' => $data['placeholder'] ?? $field,
                'label' => false
            ));
        }

        $form->add('reset', ResetType::class);
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $data
     * @return array
     */
    protected function getChoices(QueryBuilder $queryBuilder, array $data) {
        $property = $data['property'] ?? 'name';
        $unique = isset($data['unique']) && $data['unique'];

        $queryBuilder = (clone $queryBuilder)
                                ->resetDQLPart('select')
                                ->resetDQLPart('groupBy')
                                ->resetDQLPart('orderBy')
                                ->select(
                                    sprintf('MAX(%s.id) HIDDEN _', $queryBuilder->getRootAliases()[0]),
                                    sprintf('%s.id', $data['alias']),
                                    sprintf('%s.%s', $data['alias'], $property)
                                )
                                ->groupBy(sprintf('%s.id', $data['alias']))
                                ->orderBy(sprintf('%s.%s', $data['alias'], $property), 'ASC');

        $result = $queryBuilder
                    ->getQuery()
                        ->getArrayResult();

        return array_column($result, $property, $unique ? $property : 'id');

    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
        $resolver->setRequired(['class', 'query_builder', 'fields']);
        $resolver->setAllowedTypes([
            'query_builder' => array(QueryBuilder::class, \Closure::class),
            'class'         => 'string',
            'fields'        => 'array',
        ]);
    }
}
