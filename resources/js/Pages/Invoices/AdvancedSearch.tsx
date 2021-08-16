import React, { useEffect, useRef, useState } from 'react';
import { FormHandles, SubmitHandler } from '@unform/core';
import { Form } from '@unform/web';
import axios from 'axios';
import Select from '../../Shared/Components/Select';
import Input from '../../Shared/Components/Input';
import DatePicker from '../../Shared/Components/DatePicker';
import NumberInput from '../../Shared/Components/NumberInput';

interface ICliente {
    id: string;
    social_name: string;
    value?: string;
    label?: string;
}

interface FormData {
    customer?: string;
    data_ini?: Date;
    data_fin?: Date;
    nf_ini?: number;
    nf_fin?: number;
    agente?: string;
    agente2?: string;
    saldo_ini?: number;
    saldo_fin?: number;
}

const AdvancedSearch: React.FC = () => {
    const [startDate, setStartDate] = useState(new Date());
    const [clientes, setClientes] = useState<ICliente[]>([]);
    const formRef = useRef<FormHandles>(null)

    useEffect(() => {
        axios.get<ICliente[]>('api/customers')
            .then((response) => {
                const mappedData = response?.data?.map((data) => {
                    return {
                        ...data,
                        value: data.id,
                        label: data.social_name
                    }
                })
                setClientes(mappedData);
            })
    }, []);

    const handleSubmit: SubmitHandler<FormData> = data => {
        axios.get('api/invoices', {
            params: {
                customer_id: data.customer,
                data_ini: data.data_ini,
                data_fin: data.data_fin,
                nf_ini: data.nf_ini,
                nf_fin: data.nf_fin,
            }
        });
    }


    return (
        <Form ref={formRef} onSubmit={handleSubmit} >
            <div className="bg-white rounded shadow p-2 mb-8">
                <div className="mx-2 flex">
                    <div className="border-4 flex-1">
                        <label htmlFor="customer">Clientes</label>
                        <Select
                            name="customer"
                            options={clientes}
                        />
                    </div>

                    <div className="border-4 w-1/4">
                        <label htmlFor="customer">Clientes</label>
                        <Select
                            name="agente"
                            options={clientes}
                        />
                    </div>

                    <div className="border-4 w-1/4">
                        <label htmlFor="customer">Clientes</label>
                        <Select
                            name="agente2"
                            options={clientes}
                        />
                    </div>
                </div>
                <div className="mx-2 flex">
                    <div className="border-4 w-1/4">
                        <Input
                            name="nf_ini"
                            label="Nº Nota Inicial"
                        />
                    </div>

                    <div className="border-4 w-1/4">
                        <Input
                            name="nf_fin"
                            label="Nº Nota Final"
                        />
                    </div>

                    <div className="border-4 w-1/4">
                        <DatePicker
                            name="data_ini"
                            label="Data Inicial"
                        />
                    </div>
                    <div className="border-4 w-1/4">
                        <DatePicker
                            name="data_fin"
                            label="Data Final"
                        />
                    </div>
                </div>
                <div className="mx-2 flex">
                    <div className="border-4 w-1/4">
                        <Input
                            name="nf_ini"
                            label="Saldo Inicial"
                        />
                    </div>

                    <div className="border-4 w-1/4">
                        <Input
                            name="nf_fin"
                            label="Saldo Final"
                        />
                    </div>

                    <div className="border-4 w-1/4">
                        <NumberInput
                            prefix={'R$'}
                            name="data_ini"
                            label="Saldo Cliente Inicial"
                        />
                    </div>

                    <div className="border-4 w-1/4">
                        <NumberInput
                            prefix={'R$'}
                            name="data_ini"
                            label="Saldo Cliente Final"
                        />
                    </div>
                </div>
                <button type="submit">Pesquisar</button>
            </div>

        </Form>

    );
}

export default AdvancedSearch;



{/*  */ }
