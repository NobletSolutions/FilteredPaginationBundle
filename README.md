Installation
============

    composer require ns/filtered-pagination-bundle

Edit AppKernel.php and add the bundle

    ...
    new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
    new Lexik\Bundle\FormFilterBundle\LexikFormFilterBundle(),
    new NS\FilteredPaginationBundle\NSFilteredPaginationBundle(),

Usage
============

In a controller you request the filtered pagination bundle.

    $query             = $this->get('doctrine.orm.entity_manager')->getRepository('...')->getSomeQuery();
    $filteredPaginator = $this->get('ns.filtered_pagination');
    list($form, $pagination, $redirect) = $filteredPaginator->process($request, $formType, $query, 'sessionKey');

    if ($redirect) {
        return $this->redirect($this->generateUrl('practiceUsers'));
    }

Limit Select
============

You can use the provided LimitSelectType to provide a 'X' results per page selector. If you do so
you should include the main.js resource which handles detecting changes to the number per page.

