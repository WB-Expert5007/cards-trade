@extends('frontend.layout.app')

@section('content')

<main class="main cart">
    <!-- Start of Breadcrumb -->
    <nav class="breadcrumb-nav">
        <div class="container">
            <ul class="breadcrumb shop-breadcrumb bb-no">
                <li class="active">Shopping Cart</li>
                <li><a href="{{ route('checkout') }}">Checkout</a></li>
                <li>Order Complete</li>
            </ul>
        </div>
    </nav>
    <!-- End of Breadcrumb -->

    <!-- Start of PageContent -->
    <div class="page-content">
        <div class="container">
            <div class="row gutter-lg mb-10">
                <div class="col-lg-8 pr-lg-4 mb-6">
                    <table class="shop-table cart-table">
                        <thead>
                            <tr>
                                <th class="product-name"><span>Product</span></th>
                                <th></th>
                                <th style="text-align: start"><span>Value</span></th>
                                <th class="product-quantity"><span>Quantity</span></th>
                                <th class="product-subtotal"><span>Subtotal</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($content))
                            @foreach ($content as $list)
                            @php if(Auth::check()) {
                                $data = $list->product;
                            } else {
                                $data = $list;
                            }
                            @endphp
                            <tr>
                                <td class="product-thumbnail">
                                    <div class="p-relative">
                                        <a href="{{ route('product.detail', $data->id) }}">
                                            <figure>
                                                @if(file_exists(public_path('storage/cards/'.$data->merchant->image)) && !empty($data->merchant->image))
                                                <img src="{{ asset('storage/cards/'.$data->merchant->image) }}" alt="{{ $data->merchant->name }}" width="84" height="94" />
                                                @else
                                                <img src="{{ asset('user-assets/images/default-card.png') }}" width="84" height="94" alt="{{ $data->merchant->name }}" />
                                                @endif
                                            </figure>
                                        </a>
                                        <button type="submit" class="btn btn-close btn-delete-item-cart" data-id="{{ $data->id }}"><i
                                                class="fas fa-times"></i></button>
                                    </div>
                                </td>
                                <td class="product-name">
                                    <a href="{{ route('product.detail', $data->id) }}">
                                        {{ $data->merchant->name }}
                                    </a>
                                </td>
                                <td><span class="text-center">${{ number_format($data->value, 2)}}</span></td>
                                <td class="text-center">
                                    {{ $data->quantity }}
                                </td>
                                <td class="product-subtotal text-center">
                                    <span class="amount" style="text-align: center">${{ number_format($data->value * ( 100 - $data->discount) / 100, 2)}}</span>
                                </td>
                            </tr>
                            @endforeach

                            @else
                            <tr>
                                <td colspan="5" class="text-center">No Product exist in your shopping cart.</td>
                            </tr>


                            @endif

                        </tbody>
                    </table>

                    <div class="cart-action mb-6">
                        <a href="{{ route('buy') }}" class="btn btn-dark btn-rounded btn-icon-left btn-shopping mr-auto"><i class="w-icon-long-arrow-left"></i>Continue Shopping</a>
                        {{-- <button type="submit" class="btn btn-rounded btn-default btn-clear" name="clear_cart" value="Clear Cart">Clear Cart</button>
                        <button type="submit" class="btn btn-rounded btn-update disabled" name="update_cart" value="Update Cart">Update Cart</button> --}}
                    </div>

                    {{-- <form class="coupon">
                        <h5 class="title coupon-title font-weight-bold text-uppercase">Coupon Discount</h5>
                        <input type="text" class="form-control mb-4" placeholder="Enter coupon code here..." required />
                        <button class="btn btn-dark btn-outline btn-rounded">Apply Coupon</button>
                    </form> --}}
                </div>
                <div class="col-lg-4 sticky-sidebar-wrapper">
                    <div class="sticky-sidebar">
                        <div class="cart-summary mb-4">
                            <h3 class="cart-title text-uppercase">Cart Totals</h3>
                            <div class="cart-subtotal d-flex align-items-center justify-content-between">
                                <label class="ls-25">Subtotal</label>
                                <span id="tag_subtotal">${{ number_format($sub_total, 2)}}</span>
                            </div>

                            {{-- <hr class="divider"> --}}

                            {{-- <ul class="shipping-methods mb-2"> --}}
                                {{-- <li>
                                    <label
                                        class="shipping-title text-dark font-weight-bold">Shipping</label>
                                </li>
                                <li>
                                    <div class="custom-radio">
                                        <input type="radio" id="free-shipping" class="custom-control-input"
                                            name="shipping">
                                        <label for="free-shipping"
                                            class="custom-control-label color-dark">Free
                                            Shipping</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="custom-radio">
                                        <input type="radio" id="local-pickup" class="custom-control-input"
                                            name="shipping">
                                        <label for="local-pickup"
                                            class="custom-control-label color-dark">Local
                                            Pickup</label>
                                    </div>
                                </li>
                                <li>
                                    <div class="custom-radio">
                                        <input type="radio" id="flat-rate" class="custom-control-input"
                                            name="shipping">
                                        <label for="flat-rate" class="custom-control-label color-dark">Flat
                                            rate:
                                            $5.00</label>
                                    </div>
                                </li>
                            </ul>

                            <div class="shipping-calculator">
                                <p class="shipping-destination lh-1">Shipping to <strong>CA</strong>.</p>

                                <form class="shipping-calculator-form">
                                    <div class="form-group">
                                        <div class="select-box">
                                            <select name="country" class="form-control form-control-md">
                                                <option value="default" selected="selected">United States
                                                    (US)
                                                </option>
                                                <option value="us">United States</option>
                                                <option value="uk">United Kingdom</option>
                                                <option value="fr">France</option>
                                                <option value="aus">Australia</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="select-box">
                                            <select name="state" class="form-control form-control-md">
                                                <option value="default" selected="selected">California
                                                </option>
                                                <option value="ohaio">Ohaio</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input class="form-control form-control-md" type="text"
                                            name="town-city" placeholder="Town / City">
                                    </div>
                                    <div class="form-group">
                                        <input class="form-control form-control-md" type="text"
                                            name="zipcode" placeholder="ZIP">
                                    </div>
                                    <button type="submit" class="btn btn-dark btn-outline btn-rounded">Update
                                        Totals</button> --}}
                                {{-- </form>
                            </div> --}}

                            <hr class="divider mb-6">
                            <div class="order-total d-flex justify-content-between align-items-center">
                                <label>Total</label>
                                <span class="ls-50" id="tag_total">${{ number_format($sub_total, 2)}}</span>
                            </div>
                            <a href="{{ route('checkout') }}"
                                class="btn btn-block btn-dark btn-icon-right btn-rounded  btn-checkout">
                                Proceed to checkout<i class="w-icon-long-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of PageContent -->
</main>

@endsection

@push('page-script')

<script type="text/javascript">
    $(document).ready(function (){
        $('.btn-delete-item-cart').on('click', function(e){
            e.preventDefault();
            var i = $(this);
            $.ajax({
                type: "POST",
                url: "{{ route('cart.delete') }}",
                data: {
                    product_id: i.attr('data-id')
                },
                headers: {
                    'X-CSRF-Token': $('meta[name=csrf_token]').attr('content')
                },
                success: function (response) {
                    if(response.status == 'error') {
                        toastr['error'](response.message);
                    }
                    else if(response.status == 'success'){
                        toastr['success'](response.message);
                        $('#div-shopping-cart').html(response.cart);
                        $(".cart-toggle").click(function () {
                            $("body").toggleClass("cart-opened");
                        }),
                        $(".btn-close").click(function () {
                            $("body").toggleClass("cart-opened");
                            }),
                            $(".box-close").click(function () {
                                $(this).parent().remove();
                            }),
                            $(".cart-overlay").click(function (e) {
                                $("body").removeClass("cart-opened");
                            });

                        $('#tag_subtotal').html('$' + response.subtotal), $('#tag_total').html('$' + response.subtotal);
                        i.closest("tr").remove();
                    } else {
                        toastr['warning']('Something went wrong, please try again.');
                    }
                },
                error: function(response) {
                    toastr['error']('Server Connection Failed');
                }
            });
        });
    });
</script>

@endpush
