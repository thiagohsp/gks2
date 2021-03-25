import React, { useRef, useState, useEffect } from 'react';
import NumberFormat, { NumberFormatProps } from 'react-number-format';
import { useField } from '@unform/core';
import { Container } from './styles'

import 'react-datepicker/dist/react-datepicker.css';

interface Props extends Omit<NumberFormatProps, 'onChange'> {
    name: string;
    value?: number;
    disabled?: boolean;
    defaultValue?: number;
    label?: string;
}
export default function NumberInput({ name, label, ...rest }: Props) {
    const inputRef = useRef<NumberFormat>(null)
    const { fieldName, defaultValue, registerField, error } = useField(name);
    useEffect(() => {
        console.log();
        registerField({
            name: fieldName,
            ref: inputRef.current,
            getValue: ref => {
                return Number(ref.state.numAsString)
            },
            setValue: (ref, value) => {
                ref.state.value = value
            },
            clearValue: ref => {
                ref.state.value = ''
            },
        })
    }, [fieldName, registerField])

    return (
        <Container>
            {label && <label htmlFor={fieldName}>{label}</label>}
            <NumberFormat
                ref={inputRef}
                decimalSeparator=","
                thousandSeparator="."
                decimalScale={2}
                fixedDecimalScale={true}
                style={{ textAlign: 'right' }}
                {...rest}
            />
            {error && <span>{error}</span>}
        </Container>
    );
};
