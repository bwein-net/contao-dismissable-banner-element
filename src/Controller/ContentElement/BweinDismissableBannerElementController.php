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

use Contao\Config;
use Contao\ContentModel;
use Contao\CoreBundle\Asset\ContaoContext;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\Framework\FrameworkAwareInterface;
use Contao\CoreBundle\Framework\FrameworkAwareTrait;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("dismissableBanner", category="banner", template="ce_dismissable_banner")
 */
class BweinDismissableBannerElementController extends AbstractContentElementController implements FrameworkAwareInterface
{
    use FrameworkAwareTrait;

    private $scopeMatcher;
    private $assetsContext;

    public function __construct(ScopeMatcher $scopeMatcher, ContaoContext $assetsContext)
    {
        $this->scopeMatcher = $scopeMatcher;
        $this->assetsContext = $assetsContext;
    }

    public function __invoke(Request $request, ContentModel $model, string $section, array $classes = null): Response
    {
        $response = parent::__invoke($request, $model, $section, $classes);
        $this->addSharedMaxAgeToResponse($response, $model);

        return $response;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $this->framework->initialize();

        if (!$model->dismissableBannerExpiryLimit) {
            $template->dismissableBannerExpiryTime = 30 * 24 * 60 * 60;
        }

        $this->addTextParamsToTemplate($template, $model, $request);
        $this->addHyperlinkParamsToTemplate($template, $model, $request);

        return $template->getResponse();
    }

    protected function addTextParamsToTemplate(Template $template, ContentModel $model, Request $request): void
    {
        $params = ['class' => 'ce_text'];

        $model->text = StringUtil::toHtml5($model->text);

        // Add the static files URL to images
        if ($staticUrl = $this->assetsContext->getStaticUrl()) {
            $path = Config::get('uploadPath').'/';
            $model->text = str_replace(' src="'.$path, ' src="'.$staticUrl.$path, $model->text);
        }

        $params['text'] = StringUtil::encodeEmail($model->text);
        $template->textParams = $params;
    }

    protected function addHyperlinkParamsToTemplate(Template $template, ContentModel $model, Request $request): void
    {
        if (!$model->dismissableBannerAddLink) {
            return;
        }

        $params = ['class' => 'ce_hyperlink'];

        if (0 === strncmp($model->url, 'mailto:', 7)) {
            $model->url = StringUtil::encodeEmail($model->url);
        } else {
            $model->url = ampersand($model->url);
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
