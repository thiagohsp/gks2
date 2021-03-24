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
    const inputRef = useRef(null);
    const { fieldName, registerField, defaultValue, error } = useField(name);
    const [date, setDate] = useState(defaultValue || null);
    useEffect(() => {
        registerField({
            name: fieldName,
            ref: inputRef.current,
            path: 'props.selected',
            clearValue: (ref: any) => {
                ref.clear();
            },
        });
    }, [fieldName, registerField]);
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
