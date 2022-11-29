import { Formik, Form, FieldProps, Field } from 'formik';
import { observer } from 'mobx-react-lite'
import { Segment, Comment, Item, ItemContent } from 'semantic-ui-react'
import { useStore } from '../../stores/store';
import * as Yup from 'yup';
import { formatDistanceToNow } from 'date-fns';
import { useEffect, useState } from 'react';
import {Thread} from '../../models/Thread';


interface Props {
    thread: Thread;
}

export default observer(function ThreadChat({ thread}: Props) {
    
    const { postStore } = useStore();
    const { threadStore } = useStore();
    
    return (
    <Segment attached clearing>
        <Item.Group>
            <Item>
                    <ItemContent>
                        <Item.Header as="a">Thread by {thread.userName}</Item.Header>
                        <Item.Description content={thread.content}/>
                    </ItemContent>
            </Item>
        </Item.Group>
        <Comment.Group>
            {thread.posts.slice(0).reverse().map(post => (
                    <Comment key={post.id}>
                        <Comment.Content>
                            <div className="row">
                                <Comment.Author>{post.userName ?? "User"}</Comment.Author>
                                <Comment.Metadata><div>{post.createdAt}</div></Comment.Metadata>
                            </div>
                            <Comment.Text style={{ whiteSpace: 'pre-wrap' }}>{post.content}</Comment.Text>
                        </Comment.Content>
                    </Comment>
                ))}
            </Comment.Group>
            <Formik
                onSubmit={(values, { resetForm }) => postStore.addPost(values).then(() => resetForm())}
                initialValues={{ body: '' }}
                validationSchema={Yup.object({
                body: Yup.string().required()
                })}
            >
                {({ isValid, handleSubmit }) => (
                    <Form className='ui form'>
                        <Field name='body'>
                            {(props: FieldProps) => (
                                <div style={{ position: 'relative' }}>
                                    <textarea
                                        placeholder='Enter your post (Enter to submit, SHIFT + enter for new line)'
                                        rows={2}
                                        {...props.field}
                                        onKeyPress={e => {
                                            if (e.key === 'Enter' && e.shiftKey) {
                                                return;
                                            }
                                            if (e.key === 'Enter' && !e.shiftKey) {
                                                e.preventDefault();
                                                isValid && handleSubmit();
                                            }
                                        }}
                                    />
                                </div>
                            )}
                        </Field>
                    </Form>
                )}
            </Formik>
        </Segment>
    )
});