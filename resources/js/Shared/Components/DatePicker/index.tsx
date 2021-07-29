import React, { useRef, useState, useEffect } from 'react';
import ReactDatePicker, { ReactDatePickerProps, registerLocale } from 'react-datepicker';
import { useField } from '@unform/core';
import ptBR from "date-fns/locale/pt-BR"; // the locale you want

registerLocale("pt-BR", ptBR);


import { Container } from './styles'

import 'react-datepicker/dist/react-datepicker.css';

interface Props extends Omit<ReactDatePickerProps, 'onChange'> {
    name: string;
    label?: string;
}
export default function DatePicker({ name, label, ...rest }: Props) {
    const datepickerRef = useRef(null);
    const { fieldName, registerField, defaultValue, error } = useField(name);
    const [date, setDate] = useState(defaultValue || null);
    useEffect(() => {
        registerField({
            name: fieldName,
            ref: datepickerRef.current,
            path: 'props.selected',
            clearValue: (ref: any) => {
                ref.clear();
            },
        });
    }, [fieldName, registerField]);
    return (
        <Container>
            {label && <label htmlFor={fieldName}>{label}</label>}
            <ReactDatePicker
                ref={datepickerRef}
                selected={date}
                onChange={setDate}
                locale="pt-BR"
                dateFormat="dd/MM/yyyy"
                {...rest}
            />
            {error && <span>{error}</span>}
        </Container>
    );
};
