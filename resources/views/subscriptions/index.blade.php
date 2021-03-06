<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Checkout') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div id="show-errors" style="display: none;" class="mt-2 text-sm text-red-600"></div>

                    <p>Assinando o: {{ $plan->name }}</p>
                    <form action="{{ route('subscription.store') }}" method="post" id="form">
                        @csrf

                        <div class="col-span-6 sm:col-span-4 py-2">
                            <input type="text" name="hold_name" id="card-holder-name" placeholder="Nome do Cartão" class="">
                        </div>    
                    
                        <div class="col-span-6 sm:col-span-4 py-2">
                            <div id="card-element"></div>
                        </div>

                        <div class="col-span-6 sm:col-span-4 py-2">
                            <button id="card-buttom" data-secret="{{ $intent->client_secret }}" type="submit" class="inline-flex justify">Enviar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>

const stripe = Stripe("{{ config('cashier.key') }}");
const elements = stripe.elements();
const cardElement = elements.create('card');
cardElement.mount('#card-element')

// subscription payment
const form = document.getElementById('form')
const cardHolderName = document.getElementById('card-holder-name')
const cardButton = document.getElementById('card-buttom')
const clientSecret = cardButton.dataset.secret

const showErrors = document.getElementById('show-errors')

form.addEventListener('submit', async(e) => {
    e.preventDefault()

    // Disable Button
    cardButton.classList.add('cursor-not-allowed')
    cardButton.firstChild.data = 'Validando'

    //Reset Errors
    showErrors.innerText = ''
    showErrors.style.display = 'none'

    const { setupIntent, error } = await stripe.confirmCardSetup(
        clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: cardHolderName.value
                }
            }
        }
    );

    if(error) {
        console.log(error)

        showErrors.style.display = 'block'
        showErrors.innerText = (error.type == 'validation_error') ? error.message : 'Dados inválidos, verifique e tente novamente !!!'

        cardButton.classList.remove('cursor-not-allowed')
        
        return;
    }

    let token = document.createElement('input')
    token.setAttribute('type', 'hidden')
    token.setAttribute('name', 'token')
    token.setAttribute('value', setupIntent.payment_method)
    form.appendChild(token)

    form.submit()
})
</script>

<style>
    .StripeElement {
        background-color: white;
        padding: 8px 12px;
        border-radius: 4px;
        border: 1px solid transparent;
        box-shadow: 0 1px 3px 0 #e6ebf1;
        -webkit-transition: box-shadow 150ms ease;
        transition: box-shadow 150ms ease;
    }
    .StripeElement--focus {
        box-shadow: 0 1px 3px 0 #cfd7df;
    }
    .StripeElement--invalid {
        border-color: #fa755a;
    }
    .StripeElement--webkit-autofill {
        background-color: #fefde5 !important;
    }
</style>