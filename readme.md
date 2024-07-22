
# easyHcaptcha

This package provides an easy way to integrate hCaptcha into your Symfony application.

## Installation

```bash
composer require ethsam/easy-hcaptcha
```

## Configuration

Add the following to your `.env` file:

```ini
HCAPTCHA_SITE_KEY=your_site_key
HCAPTCHA_SECRET_KEY=your_secret_key
```

Add the following to `config/packages/hcaptcha.yaml`:

```yaml
parameters:
    hcaptcha_site_key: '%env(HCAPTCHA_SITE_KEY)%'
    hcaptcha_secret_key: '%env(HCAPTCHA_SECRET_KEY)%'
```

## Usage

### In your Twig template, include the hCaptcha widget and script:

```twig
<!DOCTYPE html>
<html>
<head>
    <title>{{ title }}</title>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body>
    <form class="cons-contact-form" method="post" action="{{ path('app_contact_demande') }}">
        <!-- Other form fields -->
        <div class="h-captcha" data-sitekey="{{ hcaptcha_site_key }}"></div>
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
```

### In your controller, verify the captcha response:

```php
use Ethsam\EasyHcaptcha\HcaptchaVerifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ContactController extends AbstractController
{
    private $mailer;
    private $hcaptchaVerifier;
    private $params;

    public function __construct(MailerInterface $mailer, HcaptchaVerifier $hcaptchaVerifier, ParameterBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->hcaptchaVerifier = $hcaptchaVerifier;
        $this->params = $params;
    }

    #[Route('/nous-contacter', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
            'title' => 'Nous contacter',
            'contactLink' => true,
            'hcaptcha_site_key' => $this->params->get('hcaptcha_site_key'),
        ]);
    }

    #[Route('/votre-demande', name: 'app_contact_demande', methods: "POST")]
    public function formContact(Request $request)
    {
        $dataArray = [
            "user" => $request->request->get('user'), //just for bot
            "username" => $request->request->get('username'),
            "email" => $request->request->get('email'),
            "raisonsocial" => $request->request->get('raisonsocial'),
            "phone" => $request->request->get('phone'),
            "objet" => $request->request->get('objet'),
            "message" => $request->request->get('message'),
            "captcha" => $request->request->get('h-captcha-response'),
        ];

        if (empty($dataArray['user']) && !empty($dataArray['username']) && !empty($dataArray['email']) && !empty($dataArray['raisonsocial']) && !empty($dataArray['phone']) && !empty($dataArray['message']) && !empty($dataArray['captcha'])) {
            $captchaVerification = $this->hcaptchaVerifier->verify($dataArray['captcha']);

            if ($captchaVerification) {
                // Process the form
            } else {
                // Handle the error
            }
        } else {
            // Handle the error
        }

        return $this->render('contact/demande.html.twig', [
            'controller_name' => 'ItemsController',
            'noIndex' => true,
            'contactLink' => true,
            'title' => 'Votre demande',
            'statusMail' => $statusSend,
            'hcaptcha_site_key' => $this->params->get('hcaptcha_site_key'),
        ]);
    }
}
```
