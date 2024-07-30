import Cleave from 'cleave.js';

window.opayo = ({ processing, identifier, merchantKey, name, $wire }) => {
  return {
    // We use AlpineJs modelling here as we do not want the card details to go up to Livewire.
    name: name,
    card: null,
    expiry: null,
    cvv: null,
    cardType: null,
    processing: processing,
    // This is the tokenised card we need to send up to Livewire
    identifier: identifier,
    merchantKey: merchantKey,
    errors: [],
    init() {

      window.addEventListener('opayo_threed_secure_response', e => {
          $wire.call('processThreed', {
            mdx: e.detail.mdx,
            md: e.detail.md,
            pares: e.detail.PaRes,
            cres: e.detail.cres
          })
      });

      new Cleave(this.$refs['card'], {
        creditCard: true,
        onCreditCardTypeChanged: type => {
            this.cardType = type;
        }
      });

      new Cleave(this.$refs['expiry'], {
        date: true,
        datePattern: ['m', 'y']
      });
    },
    handleSubmit () {
      this.errors = []
      this.processing = true

      const date = new Date();
      const tzOffset = date.getTimezoneOffset();

      let screenSize = 'Large';

      if (window.outerWidth < 400) {
          screenSize = 'Small';
      }

      if (window.outerWidth < 800) {
          screenSize = 'Medium';
      }

      let colorDepth = window.screen.colorDepth;
      const supportedDepths = [1, 4, 8, 15, 16, 24, 32, 48];

      if (!supportedDepths.includes(colorDepth)) {
          colorDepth = 24;
      }

      $wire.set('browser', {
        browserLanguage: navigator.language,
        challengeWindowSize: screenSize,
        browserUserAgent: navigator.userAgent,
        browserJavaEnabled: navigator.javaEnabled(),
        browserColorDepth: colorDepth,
        browserScreenHeight: window.outerHeight,
        browserScreenWidth: window.outerWidth,
        browserTZ: tzOffset,
      })

      sagepayOwnForm({
        merchantSessionKey: this.merchantKey,
      }).tokeniseCardDetails({
        onTokenised: (result) => {
            if (!result.success) {
              this.errors = result.errors
              $wire.set('processing', false)
              // {{-- return --}}
            } else {
              $wire.set('identifier', result.cardIdentifier)
              $wire.set('sessionKey', this.merchantKey)
              $wire.call('process')
            }
        },
        cardDetails: {
          cardholderName: this.name,
          cardNumber: this.card.toString().replace(/\s/g,''),
          expiryDate: this.expiry.toString().replace('/', ''),
          securityCode: this.cvv,
        }
      })
    }
  }
}
