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
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Image\Studio\Studio;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\StringUtil;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement('dismissableBanner', category: 'banner', template: 'ce_dismissable_banner')]
class BweinDismissableBannerElementController extends AbstractContentElementController
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly ContaoContext $assetsContext,
        private readonly ParameterBagInterface $params,
        private readonly Studio $studio,
    ) {
    }

    public function __invoke(Request $request, ContentModel $model, string $section, array|null $classes = null): Response
    {
        $response = parent::__invoke($request, $model, $section, $classes);
        $this->addSharedMaxAgeToResponse($response, $model);

        return $response;
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $this->framework->initialize();

        if (!$model->dismissableBannerExpiryLimit) {
            $template->dismissableBannerExpiryTime = 30 * 24 * 60 * 60;
        }

        $this->addTextParamsToTemplate($template, $model);
        $this->addHyperlinkParamsToTemplate($template, $model, $request);
        $template->contentModel = $model;

        return $template->getResponse();
    }

    /**
     * Parameters for the "ce_text" template, which is included without context.
     */
    protected function addTextParamsToTemplate(FragmentTemplate $template, ContentModel $model): void
    {
        $text = (string) $model->text;

        // Add the static files URL to images
        if ($staticUrl = $this->assetsContext->getStaticUrl()) {
            $path = $this->params->get('contao.upload_path').'/';
            $text = str_replace(' src="'.$path, ' src="'.$staticUrl.$path, $text);
        }

        $params = new \stdClass();
        $params->class = 'ce_text';
        $params->cssID = '';
        $params->text = StringUtil::encodeEmail($text);
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
     * Parameters for the "ce_hyperlink" template, which is included without context.
     *
     * The values are escaped in the Twig template, so they must not be encoded here.
     */
    protected function addHyperlinkParamsToTemplate(FragmentTemplate $template, ContentModel $model, Request $request): void
    {
        if (!$model->dismissableBannerAddLink) {
            return;
        }

        $url = (string) $model->url;

        if (str_starts_with($url, 'mailto:')) {
            $url = StringUtil::encodeEmail($url);
        }

        $params = [
            'class' => 'ce_hyperlink',
            'cssID' => '',
            'useImage' => false,
            'href' => $url,
            'link' => $model->linkTitle ?: $url,
            'linkTitle' => $model->titleText ?: null,
            'attribute' => $model->rel ? ['data-lightbox' => $model->rel] : [],
            'target' => $model->target ? ['target' => '_blank'] : [],
            'rel' => $model->target ? ['rel' => 'noreferrer noopener'] : [],
        ];

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $params['linkTitle'] = null;
        }

        $template->hyperlinkParams = $params;
    }
}
