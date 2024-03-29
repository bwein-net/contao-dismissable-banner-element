<?php

declare(strict_types=1);

/*
 * This file is part of Dismissable Banner Element for Contao Open Source CMS.
 *
 * (c) bwein.net
 *
 * @license MIT
 */

namespace Bwein\DismissableBannerElement\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Asset\ContaoContext;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("dismissableBanner", category="banner", template="ce_dismissable_banner")
 */
class BweinDismissableBannerElementController extends AbstractContentElementController
{
    private ContaoFramework $framework;
    private ScopeMatcher $scopeMatcher;
    private ContaoContext $assetsContext;
    private ParameterBagInterface $params;
    private Studio $studio;

    public function __construct(ContaoFramework $framework, ScopeMatcher $scopeMatcher, ContaoContext $assetsContext, ParameterBagInterface $params, Studio $studio)
    {
        $this->framework = $framework;
        $this->scopeMatcher = $scopeMatcher;
        $this->assetsContext = $assetsContext;
        $this->params = $params;
        $this->studio = $studio;
    }

    public function __invoke(Request $request, ContentModel $model, string $section, array $classes = null): Response
    {
        $response = parent::__invoke($request, $model, $section, $classes);
        $this->addSharedMaxAgeToResponse($response, $model);

        return $response;
    }

    /**
     * @param Template|FragmentTemplate $template
     */
    protected function getResponse($template, ContentModel $model, Request $request): Response
    {
        $this->framework->initialize();

        if (!$model->dismissableBannerExpiryLimit) {
            $template->dismissableBannerExpiryTime = 30 * 24 * 60 * 60;
        }

        $this->addTextParamsToTemplate($template, $model, $request);
        $this->addHyperlinkParamsToTemplate($template, $model, $request);
        $template->contentModel = $model;

        return $template->getResponse();
    }

    /**
     * @param Template|FragmentTemplate $template
     */
    protected function addTextParamsToTemplate($template, ContentModel $model, Request $request): void
    {
        $params = new \stdClass();
        $params->class = 'ce_text';

        // Add the static files URL to images
        if ($staticUrl = $this->assetsContext->getStaticUrl()) {
            $path = $this->params->get('contao.upload_path').'/';
            $model->text = str_replace(' src="'.$path, ' src="'.$staticUrl.$path, (string) $model->text);
        }

        $params->text = StringUtil::encodeEmail((string) $model->text);
        $params->addImage = false;
        $params->addBefore = false;

        $figure = !$model->addImage ? null : $this->studio
            ->createFigureBuilder()
            ->fromUuid($model->singleSRC ?: '')
            ->setSize($model->size)
            ->setMetadata($model->getOverwriteMetadata())
            ->enableLightbox((bool) $model->fullsize)
            ->buildIfResourceExists()
        ;

        if (null !== $figure) {
            $figure->applyLegacyTemplateData($params, $model->imagemargin, $model->floating);
        }

        $template->textParams = get_object_vars($params);
    }

    /**
     * @param Template|FragmentTemplate $template
     */
    protected function addHyperlinkParamsToTemplate($template, ContentModel $model, Request $request): void
    {
        if (!$model->dismissableBannerAddLink) {
            return;
        }

        $params = ['class' => 'ce_hyperlink'];

        if (0 === strncmp($model->url, 'mailto:', 7)) {
            $model->url = StringUtil::encodeEmail($model->url);
        } else {
            $model->url = StringUtil::ampersand($model->url);
        }

        if ($model->rel) {
            $params['attribute'] = ' data-lightbox="'.$model->rel.'"';
        }

        if (!$model->linkTitle) {
            $model->linkTitle = $model->url;
        }

        $params['href'] = $model->url;
        $params['link'] = $model->linkTitle;
        $params['target'] = '';
        $params['rel'] = '';

        if ($model->titleText) {
            $params['linkTitle'] = StringUtil::specialchars($model->titleText);
        }

        // Override the link target
        if ($model->target) {
            $params['target'] = ' target="_blank"';
            $params['rel'] = ' rel="noreferrer noopener"';
        }

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $params['title'] = '';
            $params['linkTitle'] = '';
        }

        $template->hyperlinkParams = $params;
    }
}
