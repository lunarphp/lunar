// Prop shapes for the order fulfilment components, mirroring the payload
// built by OrderShowController.

export interface FulfilmentLineData {
    id: number;
    order_line_id?: number;
    quantity: number;
    line_quantity: number;
    description: string | null;
    option: string | null;
    identifier: string | null;
    thumbnail: string | null;
    unit_price: string | null;
    sub_total: string | null;
    discount_total: string | null;
    tax: { label: string; amount: string | null }[];
    total: string | null;
    notes: string | null;
}

export interface FulfilmentTrackingData {
    id: number;
    carrier: string | null;
    carrier_name: string | null;
    shipping_method: string | null;
    tracking_number: string | null;
    url: string | null;
    destroy_url: string;
}

export interface FulfilmentTransitionData {
    state: string;
    label: string;
    via: 'ship' | 'fulfil' | 'return' | 'transition';
    notify: boolean;
}

export type FulfilmentStateCategory = 'outstanding' | 'fulfilled' | 'returned' | 'cancelled';

export interface FulfilmentData {
    id: number;
    reference: string;
    method: string;
    method_label: string;
    state: string;
    state_label: string;
    state_category: FulfilmentStateCategory;
    on_hold: boolean;
    hold_reason_label: string | null;
    hold_note: string | null;
    location: string | null;
    location_id: number;
    shipped_at: string | null;
    handed_over_label: string;
    fulfil_label: string;
    delivery_method: string | null;
    notes: string | null;
    lines: FulfilmentLineData[];
    trackings: FulfilmentTrackingData[];
    transitions: FulfilmentTransitionData[];
    can: {
        split: boolean;
        merge: boolean;
        change_location: boolean;
        add_tracking: boolean;
        undo_return: boolean;
        hold: boolean;
        release: boolean;
        cancel: boolean;
    };
    merge_targets: { id: number; reference: string; quantity: number }[];
    urls: {
        ship: string;
        fulfil: string;
        transition: string;
        split: string;
        merge: string;
        return: string;
        undoReturn: string;
        hold: string;
        release: string;
        cancel: string;
        location: string;
        trackings: string;
    };
}

export interface RefundableLineData extends FulfilmentLineData {
    refundable_quantity: number;
    refund_unit_amount: number;
}

export interface ShippingLineData {
    id: number;
    description: string | null;
    total: string | null;
    amount: number;
}

export interface SettlementData {
    status: 'balanced' | 'outstanding' | 'refund_due';
    captured: string | null;
    refunded: string | null;
    total: string;
    variance: string | null;
    varianceMajor: number;
}

export interface CarrierData {
    key: string;
    name: string;
    services: Record<string, string>;
}

export interface LocationData {
    id: number;
    name: string;
}
