<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* modules/webform/templates/webform-handler-email-summary.html.twig */
class __TwigTemplate_5aa0535b27683558453e4bef4bd8744254376ed573ff3427f7c5083f630e68ce extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $tags = ["if" => 14];
        $filters = ["t" => 14, "escape" => 15, "replace" => 15, "join" => 21];
        $functions = [];

        try {
            $this->sandbox->checkSecurity(
                ['if'],
                ['t', 'escape', 'replace', 'join'],
                []
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 13
        echo "
";
        // line 14
        if ($this->getAttribute(($context["settings"] ?? null), "debug", [])) {
            echo "<b class=\"color-error\">";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("Debugging is enabled"));
            echo "</b><br />";
        }
        // line 15
        echo "<b>";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("To:"));
        echo "</b> ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, twig_replace_filter($this->sandbox->ensureToStringAllowed($this->getAttribute(($context["settings"] ?? null), "to_mail", [])), ["," => ", "]), "html", null, true);
        echo "<br />
";
        // line 16
        if ($this->getAttribute(($context["settings"] ?? null), "cc_mail", [])) {
            echo "<b>";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("CC:"));
            echo "</b> ";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, twig_replace_filter($this->sandbox->ensureToStringAllowed($this->getAttribute(($context["settings"] ?? null), "cc_mail", [])), ["," => ", "]), "html", null, true);
            echo "<br />";
        }
        // line 17
        if ($this->getAttribute(($context["settings"] ?? null), "bcc_mail", [])) {
            echo "<b>";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("BCC:"));
            echo "</b> ";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, twig_replace_filter($this->sandbox->ensureToStringAllowed($this->getAttribute(($context["settings"] ?? null), "bcc_mail", [])), ["," => ", "]), "html", null, true);
            echo "<br />";
        }
        // line 18
        echo "<b>";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("From:"));
        echo "</b> ";
        if ($this->getAttribute(($context["settings"] ?? null), "from_name", [])) {
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["settings"] ?? null), "from_name", [])), "html", null, true);
        }
        echo " &lt;";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["settings"] ?? null), "from_mail", [])), "html", null, true);
        echo "&gt;<br />
<b>";
        // line 19
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("Subject:"));
        echo "</b> ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["settings"] ?? null), "subject", [])), "html", null, true);
        echo "<br />
<b>";
        // line 20
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("Settings:"));
        echo "</b> ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar((($this->getAttribute(($context["settings"] ?? null), "html", [])) ? ("HTML") : (t("Plain text"))));
        echo " ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(((($this->getAttribute(($context["settings"] ?? null), "html", []) && $this->getAttribute(($context["settings"] ?? null), "attachments", []))) ? ("/") : ("")));
        echo " ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar((($this->getAttribute(($context["settings"] ?? null), "attachments", [])) ? (t("Attachments")) : ("")));
        echo " ";
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar((($this->getAttribute(($context["settings"] ?? null), "twig", [])) ? (t("(Twig)")) : ("")));
        echo "<br />
<b>";
        // line 21
        echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("Sent when:"));
        echo "</b> ";
        if ($this->getAttribute(($context["settings"] ?? null), "states", [])) {
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, twig_join_filter($this->sandbox->ensureToStringAllowed($this->getAttribute(($context["settings"] ?? null), "states", [])), "; "), "html", null, true);
        } else {
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("Custom"));
        }
        echo "<br />
";
        // line 22
        if ($this->getAttribute(($context["settings"] ?? null), "theme_name", [])) {
            echo "<b>";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(t("Theme:"));
            echo "</b> ";
            echo $this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed($this->getAttribute(($context["settings"] ?? null), "theme_name", [])), "html", null, true);
            echo "<br />";
        }
    }

    public function getTemplateName()
    {
        return "modules/webform/templates/webform-handler-email-summary.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  126 => 22,  116 => 21,  104 => 20,  98 => 19,  87 => 18,  79 => 17,  71 => 16,  64 => 15,  58 => 14,  55 => 13,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "modules/webform/templates/webform-handler-email-summary.html.twig", "/Users/trentonmarks/Sites/devdesktop/replica/modules/webform/templates/webform-handler-email-summary.html.twig");
    }
}
